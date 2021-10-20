<?php

namespace tcCore\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SchoolClass;
use tcCore\Scopes\ArchivedScope;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;
use tcCore\User;

class SurveillanceController extends Controller
{
    private $response;
    private $schoolClassProgress = [];

    private $ipCheck = false;
    private $eventIdsThatRequireConfirming = null;


    public function index()
    {
        if (!Auth::user()->isA(['teacher', 'invigilator'])) {
            abort(403);
        }
        $this->setIpCheck();

        $this->response = [
            'takes'        => [],
            'participants' => [],
            'time'         => now()->format('H:i'),
            'alerts'       => 0,
            'ipAlerts'     => 0,
        ];
        $dataset = $this->getTakesForSurveillance(Auth::user());

        $dataset->each(function ($testTake) {
            $this->transformParticipants($testTake);
            $this->transformForService($testTake);
        });

        return $this->response;
    }

    private function incrementAlerts()
    {
        $this->response['alerts']++;
    }

    private function incrementIpAlerts()
    {
        $this->response['ipAlerts']++;
    }

    private function getTakesForSurveillance(User $owner)
    {
        $participantHasEvents = TestTakeEvent::select('test_participant_id', DB::Raw('max(test_take_events.id) as event'))
            ->join('test_take_event_types','test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('requires_confirming', '1')
            ->groupBy('test_participant_id');

        return TestTake::query()
            ->select(
                'test_takes.id as id',
                'test_takes.uuid as uuid',
                'tests.name as test_name'
            )
            ->withoutGlobalScope(ArchivedScope::class)
            ->join('invigilators', function ($query) use ($owner) {
                return $query
                    ->on('test_takes.id', 'invigilators.test_take_id')
                    ->where(function($query) use ($owner) {
                        $query->where('invigilators.user_id', '=', $owner->id)
                            ->orWhere('test_takes.user_id', '=', $owner->id)
                            ->orWhereIn('test_takes.id', function($query) use ($owner) {
                                $currentSchoolYearId = SchoolYearRepository::getCurrentSchoolYear()->getKey();
                                $teacherTable = with((new Teacher)->getTable());
                                $schoolClassTable = with((new SchoolClass())->getTable());
                                $query->select('test_take_id')
                                    ->from(with(new TestParticipant())->getTable())
                                    ->whereNull('deleted_at')
                                    ->whereIn('school_class_id', function ($query) use ($teacherTable,$schoolClassTable,$currentSchoolYearId){
                                        $query->select('class_id')
                                            ->from($teacherTable)
                                            ->join($schoolClassTable, "$teacherTable.class_id",'=',"$schoolClassTable.id")
                                            ->where('user_id', Auth::id())
                                            ->where('school_year_id',$currentSchoolYearId)
                                            ->whereNull("$teacherTable.deleted_at")
                                            ->whereNull("$schoolClassTable.deleted_at");
                                    })
                                    ->whereIn('test_takes.id', function ($query) use ($teacherTable,$schoolClassTable,$currentSchoolYearId){
                                        $testTable = with(new Test())->getTable();
                                        $query
                                            ->select('test_takes.id')
                                            ->from('test_takes')
                                            ->join($testTable, $testTable . '.id', '=', 'test_takes.test_id')
                                            ->whereNull($testTable.'.deleted_at')
                                            ->whereIn($testTable . '.subject_id', function ($query) use ($teacherTable,$schoolClassTable,$currentSchoolYearId){
                                                $query->select('subject_id')
                                                    ->from($teacherTable)
                                                    ->join($schoolClassTable, "$teacherTable.class_id",'=',"$schoolClassTable.id")
                                                    ->where('user_id', Auth::id())
                                                    ->where('school_year_id',$currentSchoolYearId)
                                                    ->whereNull("$teacherTable.deleted_at")
                                                    ->whereNull("$schoolClassTable.deleted_at");
                                            });
                                    });
                            });

                    });
            })
            ->join('tests', 'test_takes.test_id', 'tests.id')
            ->where('test_takes.test_take_status_id', 3)
            ->with([
                'testParticipants' => function ($query) use ($participantHasEvents) {
                    $query->select(
                        'id',
                        'test_take_id',
                        'user_id',
                        'test_take_status_id',
                        'allow_inbrowser_testing',
                        'test_take_status_id as status',
                        'ip_address',
                        'uuid',
                        'school_class_id',
                        DB::raw("case coalesce(has_events.event, 0) when 0 then 'false' else 'true' end as has_events")
                    )->addSelect(
                        [
                            'answered_count' => function ($query) {
                                $query->selectRaw('sum(score)')
                                    ->from('answers')
                                    ->join('questions', 'answers.question_id', '=', 'questions.id')
                                    ->whereRaw('test_participant_id = test_participants.id and done=1');
                            },
                            'answers_total'  => function ($query) {
                                $query->selectRaw('sum(score)')
                                    ->from('answers')
                                    ->join('questions', 'answers.question_id', '=', 'questions.id')
                                    ->whereRaw('test_participant_id = test_participants.id');
                            },
                            'ip_check'       => function ($query) {
                                $query = $this->getIpCheckQuery($query);
                            }
                        ]
                    )->leftJoinSub($participantHasEvents, 'has_events', function($join){
                       $join->on('test_participants.id', '=', 'has_events.test_participant_id');
                    });
                },
            ])
            ->get();
    }

    private function transformForService(TestTake $testTake)
    {
        $testTake->schoolClasses()->get('uuid')->each(function ($schoolClass) use ($testTake) {
            $progress = 0;
            $key = sprintf('%s_%s', $schoolClass->id, $testTake->id);

            if (array_key_exists($key, $this->schoolClassProgress) && count($this->schoolClassProgress[$key]) > 0) {

                $progress = (int) round(array_sum($this->schoolClassProgress[$key]) / count($this->schoolClassProgress[$key]));
            }
            $this->response['takes'][sprintf('progress_%s_%s', $testTake->uuid, $schoolClass->uuid)] = $progress;
        });
    }

    private function transformParticipants($testTake)
    {
        $testTake->testParticipants->each(function ($participant) {
            $hasEvents = false;
            if ($participant->has_events == 'true') {
                $hasEvents = true;
                $this->incrementAlerts();
            }

            if ($participant->test_take_status_id > 2) {
                $key = sprintf('%s_%s', $participant->school_class_id, $participant->test_take_id);
                $this->schoolClassProgress[$key][] = $this->getPercentage($participant);
            }
            $this->response['participants'][$participant->uuid] = [
                'percentage'              => $this->getPercentage($participant),
                'label'                   => $participant->label,
                'text'                    => $participant->text,
                'alert'                   => $hasEvents,
                'ip'                      => $participant->ip_check == 'true' ? true : false,
                'status'                  => $participant->test_take_status_id,
                'allow_inbrowser_testing' => $participant->allow_inbrowser_testing,
            ];
        });
    }

    private function getPercentage($participant)
    {
        if ($participant->answered_count == 0 || $participant->answers_total == 0) {
            return 0;
        }

        return (int) (round($participant->answered_count / $participant->answers_total * 100, 0));
    }

    private function getIpCheckQuery(Builder $query)
    {
        if ($this->ipCheck) {
            return $query;

        }
        $query->selectRaw("'true'");
        return $query;
    }

    private function setIpCheck()
    {
        $this->ipCheck = false;

    }

    private function getHasEventsQuery(Builder $query)
    {
        if ($this->eventIdsThatRequireConfirming == null) {
            $this->eventIdsThatRequireConfirming = TestTakeEventType::where('requires_confirming', 1)->get('id');
        }


        $query->selectRaw("case when coalesce(id, 0) > 0 then 'true' else 'false' end")
            ->from('test_take_events')
            ->whereRaw('test_take_events.test_participant_id = test_participants.id')
            ->whereIn('test_take_events.test_take_event_type_id', $this->eventIdsThatRequireConfirming)
            ->where('test_take_events.confirmed', '<>', 1)
            ->limit(1);

        return $query;
    }
}
