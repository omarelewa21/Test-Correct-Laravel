<?php

namespace tcCore\Http\Controllers;

use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Jobs\SendExceptionMail;
use tcCore\Lib\Repositories\PeriodRepository;
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


    public function index(Request $request)
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

        if($request->has('takeUuid')){
            $take_id = TestTake::whereUuid($request->get('takeUuid'))->value('id');
            if(!$take_id){
                return response(404);
            }
            $dataset = $this->getTakesForSurveillance(Auth::user(), $take_id);  
        }else{
            $dataset = $this->getTakesForSurveillance(Auth::user());
        }

        $dataset->each(function ($testTake) {
            $this->transformParticipants($testTake);
            $this->transformForService($testTake);
        });

        if (request()->boolean('withoutParticipants')) {
            collect(['participants','time','alerts','ipAlerts'])->each(function($unset){
                unset($this->response[$unset]);
            });
        }

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

    private function getTakesForSurveillance(User $owner, $take_id=null)
    {

        $test_take_ids = is_null($take_id) ? $this->getCachedTestTakeIds($owner) : [$take_id];

        $participantHasEvents = TestTakeEvent::select('test_participant_id',
            DB::Raw('max(test_take_events.id) as event'))
            ->join('test_take_event_types', 'test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('requires_confirming', '1')
            ->where('confirmed', '0')
            ->whereIn('test_take_id',$test_take_ids)
            ->groupBy('test_participant_id');


        return TestTake::select('test_takes.id as id', 'test_takes.uuid as uuid', 'tests.name as test_name')
        ->withoutGlobalScope(ArchivedScope::class)
        ->join('tests', 'test_takes.test_id', 'tests.id')
        ->whereIn('test_takes.id', $test_take_ids)
        ->where('test_take_status_id', '=', '3')
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
                )->leftJoinSub($participantHasEvents, 'has_events', function ($join) {
                    $join->on('test_participants.id', '=', 'has_events.test_participant_id');
                });
            },
        ])
        ->get();
    }

    private function transformForService(TestTake $testTake)
    {
        $testTake->schoolClasses()->get(['uuid', 'id'])->each(function ($schoolClass) use ($testTake) {
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

    private function getCachedTestTakeIds(User $owner)
    {
        $ids = cache()->remember(self::getCacheKey($owner, request()->boolean('withoutParticipants')), now()->addSeconds(60), function () use ($owner) {
            $currentPeriod =  PeriodRepository::getCurrentPeriod();
            if ($currentPeriod == null) {
                return [];
            }

            $filtered = request()->boolean('withoutParticipants') ? ['type_assignment'=> true] : ['type_not_assignment' => true];
            $filtered = array_merge($filtered, [
                'invigilator_id' => $owner->id,
                'test_take_status_id' => '3',
//                'period_id' => $currentPeriod->id,
            ]);

            return TestTake::filtered($filtered)->pluck('id');
        });

        return $ids;
    }

    public function destroy() {
        cache()->forget(self::getCacheKey(Auth::user()));
        cache()->forget(self::getCacheKey(Auth::user(), true));
    }

    private function getCacheKey($owner, $withoutParticipants = false) {
        $prefix = 'surveilence_data';
        if ($withoutParticipants) {
            $prefix = 'assignment_open_teacher_data';
        }

        return sprintf('%s_%s', $prefix, $owner->uuid);
    }
}
