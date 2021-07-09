<?php namespace tcCore\Http\Controllers\TestTakes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Jobs\SendErrorMailToSupportJob;
use tcCore\Question;
use tcCore\RttiExportLog;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTakeEvent;
use tcCore\Http\Requests\CreateTestTakeEventRequest;
use tcCore\Http\Requests\UpdateTestTakeEventRequest;
use tcCore\TestTake;

class TestTakeRttiExportController extends Controller
{

    public function store(TestTake $testTake, Request $request)
    {
        $testTake->load('test', 'testparticipants', 'test.testQuestions','test.testQuestions.question', 'schoolLocation');
        // see below for frontend handler
        $firstSchoolClass = $testTake->schoolClasses()->orderBy('name')->first();
        $informationError = null;
        try {
            $testCode = sprintf(
                '%s|%s|%s|%s',
                $testTake->test->name,
                $testTake->test->subject->abbreviation,
                optional($firstSchoolClass)->name,
                $testTake->test->getKey()
            );

            // START SETTING DATA FOR SCHOOL SECTION
            $externalMainCode = $this->getExternalMainCode($testTake);
            $rttiExportLog = null;
            try {
                $auth['aut:autorisatie'] = [
                    'autorisatiesleutel' => config('rtti.autorisatiesleutel'),
                    'klantcode' => config('rtti.klantcode'),
                    'klantnaam' => config('rtti.klantnaam')
                ];

                $leerresultatenVerzoek = [
                    'school' => [
                        'aanmaakdatum' => Carbon::now()->toAtomString(),
                        'dependancecode' => $testTake->schoolLocation->external_sub_code,
                        'brincode' => $externalMainCode,
                        'schooljaar' => $this->getSchoolYearForRttiExport($testTake),
                    ],
                    'toetsafnames' => $this->getToetsafnamesForRttiExport($testTake, $testCode),
                    'toetsen' => $this->getToetsenForRttiExport($testTake, $testCode),
                ];

                $client = new \nusoap_client(
                    config('rtti.wsdl_url'), true
                );
                $client->soap_defencoding = 'UTF-8';

                $result = $client->call(
                    'BrengLeerresultaten', [
                    'leerresultaten_verzoek' => $leerresultatenVerzoek,
                ], 'http://www.edustandaard.nl/leerresultaten/2/leerresultaten', 'leer:leerresultaten_verzoek', $auth
                );

                $rttiExportLog = RttiExportLog::create([
                   'test_take_id' => $testTake->getKey(),
                   'user_id' => Auth::id(),
                    'export' => print_r($leerresultatenVerzoek,true),
                    'result' => var_export($result,true),
                    'error' => $client->getError(),
                    'has_errors' => (bool) $client->getError()
                ]);
            } catch (\Exception $e) {
                $rttiExportLog = RttiExportLog::create([
                    'test_take_id' => $testTake->getKey(),
                    'user_id' => Auth::id(),
                    'export' => print_r($leerresultatenVerzoek,true),
                    'result' => var_export($result,true),
                    'error' => 'Fatal error '.$e->getMessage(),
                    'has_errors' => true,
                ]);
            }

        } catch (\Exception $e) {
            $rttiExportLog = RttiExportLog::create([
                'test_take_id' => $testTake->getKey(),
                'user_id' => Auth::id(),
                'export' => print_r($leerresultatenVerzoek,true),
                'error' => 'Fatal unknown error',
                'has_errors' => true,
            ]);
        }

        // if there was an error, please send an email to support
        if($rttiExportLog->has_errors === true){
            // mailtje sturen
            (new SendErrorMailToSupportJob(
                $rttiExportLog->error,
                __("Error bij het exporteren van RTTI"),
                [
                    'id' => $rttiExportLog->getKey(),
                    'test_take_id' => $rttiExportLog->test_take_id,
                    'user_id' => $rttiExportLog->user_id,
                    'timestamp' => $rttiExportLog->created_at->format('Y-m-d H:i:s')
                ]));
            // feedback geven
            return Response::make(
                __("Er is iets fout gegaan tijdens het exporteren van de gegevens naar RTTI. Neem contact op met de support desk van Test-Correct"),
                400);
        }
        // feedback van succesvolle rtti export terugsturen
        return Response::make($rttiExportLog, 200);
    }

    protected function getToetsenForRttiExport(TestTake $testTake, $testCode)
    {
        $i = 1;
        $toetsOnderdelenAr = [];

        $testTake->test->testQuestions->each(function(TestQuestion $testQuestion) use (&$toetsOnderdelenAr, &$i){
            $question = $testQuestion->question;

            if ($question->parentInstance->type == 'GroupQuestion') {

                foreach ($question->parentInstance->groupQuestionQuestions as $item) {
                    if (null === $item->question->score) {
                        $score = 0;
                    } else {
                        $score = $item->question->score;
                    }

                    $toetsOnderdelenAr['toetsonderdeel'][] = [
                        'toetsonderdeelvolgnummer' => $i,
                        'toetsonderdeelcode' => ['!' => $item->getKey()],
                        'toetsonderdeelnormering' => [
                            'toetsniveau' => ['!' => $item->parentInstance->rtti],
                            'norm' => [
                                'eindnormwaarde' => $score
                            ],
                        ],
                    ];

                    $i++;
                }
            } else {
                if (null === $question->parentInstance->score) {
                    $score = 0;
                } else {
                    $score = $question->parentInstance->score;
                }

                $toetsOnderdelenAr['toetsonderdeel'][] = [
                    'toetsonderdeelvolgnummer' => $i,
                    'toetsonderdeelcode' => ['!' => $question->getKey()],
                    'toetsonderdeelnormering' => [
                        'toetsniveau' => ['!' => $question->parentInstance->rtti],
                        'norm' => [
                            'eindnormwaarde' => $score
                        ],
                    ],
                ];

                $i++;
            }
        });

        return [
            'toetsen' => [
                'toets' => [
                    'toetscode' => ['!' => $testCode],
                    'toetssonderdelen' => $toetsOnderdelenAr
                ],
            ],
        ];
    }

    protected function getToetsafnamesForRttiExport(TestTake $testTake, $testCode)
    {
        $afnames = [];
        $testTake->testParticipants->each(function(TestParticipant $participant) use (&$afnames, $testCode, $testTake){
            $resArray = [];

            $participant->answers->each(function(Answer $answer) use (&$resArray, $testCode, $testTake){
                $resArray['resultaat'][] = [
                    'key' => $answer->getKey(),
                    'afnamedatum' => $testTake->time_start->format('Y-m-d'),
                    'toetscode' => $testCode,
                    'toetsonderdeelcode' => $answer->question_id,
                    'score' => $answer->final_rating
                ];
            });

            if (empty($resArray)) {
                return; // next please
            }

            $af = [
                'leerlingid' => $participant->user->external_id,
                'resultaatverwerkerid' => $testTake->user->external_id,
                'resultaten' => $resArray
            ];
            $afnames['toetsafname'][] = $af;
        });

        return $afnames;
    }

    protected function getSchoolYearForRttiExport(TestTake $testTake)
    {
        $yearInfo = optional(optional($testTake->testParticipants()->first()->schoolClass)->schoolYear)->year;

        if (substr_count($yearInfo, '-') > 0) {
            return $yearInfo;
        } else {
            $baseYear = (int) $yearInfo;
            if ($baseYear < 1995) {
                $baseYear = date("Y");
            }
            $nextYear = (int) $baseYear + 1;
            return sprintf('%d-%d', $baseYear, $nextYear);
        }
    }

    protected function getExternalMainCode(TestTake $testTake)
    {
        if (null !== $testTake->schoolLocation->external_main_code) {
            return $testTake->schoolLocation->external_main_code;
        }
        $schoolLocation = $testTake->schoolLocation;
        if (null === $schoolLocation->school) {
            throw new \Exception('Deze school locatie heeft geen overkoepelende school, en geen brincode, niet exporteerbaar.');
        }

        if (null !== $schoolLocation->school->external_main_code) {
            return $schoolLocation->school->external_main_code;
        }

        if (null === $schoolLocation->school->umbrella_organisation_id) {
            throw new \Exception('Deze school heeft geen brincode, en geen overkoepelende organisatie, niet exporteerbaar.');
        }

        if (null !== $schoolLocation->school->umbrella_organisatiion->external_main_code) {
            return $schoolLocation->school->umbrella_organisation->external_main_code;
        }

        throw new \Exception('Geen brincode gevonden voor deze setup, neem contact op met de helpdesk van Test Correct.');

    }

    protected function log($logString)
    {
        logger($logString);
    }
}
