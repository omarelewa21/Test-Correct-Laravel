<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Scopes\ArchivedScope;
use tcCore\TestTake;
use tcCore\User;

class SurveillanceController extends Controller
{
    private $response;

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
            $this->transformForService($testTake);

            $this->transformParticipants($testTake);
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
            ->withoutGlobalScope(new ArchivedScope)
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
                        'uuid'
                    )->leftJoin( 'test_take_events', function($join) {
                        $join->on('testparticipant.id', 'test_take_events.participant_id')
                            ->where('')
                        }
                    );
                },

            ])
            ->get();
    }

    private function transformForService(TestTake $testTake)
    {
        $testTake->schoolClasses()->get('uuid')->each(function ($schoolClass) use ($testTake) {
            $progress = 0;
            $this->response['takes'][sprintf('progress_%s_%s', $testTake->uuid, $schoolClass->uuid)] = $progress;
        });
    }

    private function transformParticipants($testTake)
    {
        $testTake->testParticipants->each(function ($participant) {
            if ($alertStatus = $participant->getAlertStatus()) {
                $this->incrementAlerts();
            }

//         dd($participant->allow_inbrowser_testing);
            $this->response['participants'][$participant->uuid] = [
                'percentage'              => 0,
                'label'                   => $participant->label,
                'text'                    => $participant->text,
                'alert'                   => $alertStatus,
                'ip'                      => true,
                'status'                  => $participant->test_take_status_id,
                'allow_inbrowser_testing' => $participant->allow_inbrowser_testing,
            ];
        });
    }
}
