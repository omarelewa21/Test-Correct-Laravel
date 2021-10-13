<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Scopes\ArchivedScope;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;

class SurveillanceController extends Controller
{
    private $response;
    private $schoolClassProgress = [];

    public function index()
    {
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
                    ->where('invigilators.user_id', '=', $owner->id);
            })
            ->join('tests', 'test_takes.test_id', 'tests.id')
            ->where('test_takes.test_take_status_id', 3)
            ->with([

                'testParticipants' => function ($query) {
                    $query->select(
                        'id',
                        'test_take_id',
                        'user_id',
                        'test_take_status_id',
                        'allow_inbrowser_testing',
                        'test_take_status_id as status',
                        'allow_inbrowser_testing',
                        'ip_address',
                        'uuid',
                        'school_class_id'
                    )->addSelect(
                        [
                            'answered_count' => function ($query) {
                                $query->selectRaw('count(*)')->from('answers')->whereRaw('test_participant_id = test_participants.id and json is not null');
                            },
                            'answers_total' => function($query) {
                                $query->selectRaw('count(*)')->from('answers')->whereRaw('test_participant_id = test_participants.id');
                            },
                        ]
                    );
                },
            ])
            ->get();
    }

    private function transformForService(TestTake $testTake)
    {
        $testTake->schoolClasses()->get('uuid')->each(function ($schoolClass) use ($testTake) {
            $progress = 0;
            $key = sprintf('%s_%s', $schoolClass->id, $testTake->id);

            if (array_key_exists($key, $this->schoolClassProgress) && count($this->schoolClassProgress[$key]) > 0){

                 $progress = (int) round(array_sum($this->schoolClassProgress[$key]) / count($this->schoolClassProgress[$key]));
            }
            $this->response['takes'][sprintf('progress_%s_%s', $testTake->uuid, $schoolClass->uuid)] = $progress;
        });
    }

    private function transformParticipants($testTake)
    {
        $testTake->testParticipants->each(function ($participant) {
            if ($alertStatus = $participant->getAlertStatus()) {
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
                'alert'                   => $alertStatus,
                'ip'                      => true,
                'status'                  => $participant->test_take_status_id,
                'allow_inbrowser_testing' => $participant->allow_inbrowser_testing,
            ];
        });
    }

    private function getPercentage($participant) {
        if ($participant->answered_count == 0 || $participant->answers_total == 0) {
            return 0;
        }

        return (int) ( round($participant->answered_count / $participant->answers_total * 100, 0));
    }
}
