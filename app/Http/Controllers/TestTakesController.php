<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\AnswerRating;
use tcCore\DiscussingParentQuestion;
use tcCore\GroupQuestion;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\NormalizeTestTakeRequest;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Question;
use tcCore\SchoolClass;
use tcCore\Shortcode;
use tcCore\Subject;
use tcCore\TemporaryLogin;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\TestParticipant;
use tcCore\Http\Requests\CreateTestTakeRequest;
use tcCore\Http\Requests\UpdateTestTakeRequest;
use tcCore\TestTakeStatus;
use tcCore\TestQuestion;
use tcCore\Exports\TestTakesExport;
use \stdClass;

class TestTakesController extends Controller {

    /**
     * Helper Function - Check if a test has one or more open questions
     *
     * @return true if check has found one open questoin - else return false
     */
    private function hasOpenQuestion($test_id){
        return !! QuestionGatherer::getQuestionsOfTest($test_id, true)->search(function(Question $question){
            return !$question->canCheckAnswer();
        });
    }


    /**
     * Display a listing of the test takes.
     *
     * @return Response
     */
    public function index(Request $request) {
        $testTakes = TestTake::filtered($request->get('filter', []), $request->get('order', []))
            ->with([
                'test',
                'test.subject' => function ($query) {
                    $query->withTrashed();
                },
                'test.author',
                'retakeTestTake',
                'user' => function ($query) {
                    $query->withTrashed();
                },
                'testTakeStatus',
                'invigilatorUsers',
                'testTakeCode'
            ]);

        $testTakes->filterByArchived(request('filter'));

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $testTakes = $this->filterIfNeededForDemo($testTakes->get());
                $testTakeSchoolClasses = [];
                foreach ($testTakes as $i => $testTake) {
                    $testTakeSchoolClasses[$i] = $testTake->schoolClasses()->get();
                }

                $testTakes = $testTakes->toArray();
                foreach ($testTakes as $i => $testTake) {
                    $testTakes[$i]['school_classes'] = $testTakeSchoolClasses[$i];
                }

                return Response::make($testTakes, 200);
                break;
            case 'list':
                $testTakes = $this->filterIfNeededForDemo($testTakes->with('testParticipants', 'testParticipants.schoolClass')->get());
                $response = [];
                foreach ($testTakes as $testTake) {
                    $test = $testTake->test;
                    if ($test instanceof Test) {
                        $haveClasses = [];
                        foreach ($testTake->testParticipants as $testParticipant) {
                            $schoolClass = $testParticipant->schoolClass;
                            if ($schoolClass instanceof SchoolClass) {
                                if (!in_array($schoolClass->getKey(), $haveClasses)) {
                                    $haveClasses[] = $schoolClass->getKey();
                                    $response[$testTake->getKey()][] = $this->getTestTakeSchoolClass($schoolClass->name, $testTake);
                                }
                            }
                        }
                        if ($testTake->testParticipants->count() == 0) {
                            $response[$testTake->getKey()][] = $this->getTestTakeSchoolClass('', $testTake);
                        }
                    }
                }
                return Response::make($response, 200);
                break;
            case 'paginate':
            default:
                $userId = Auth::user()->getKey();
                $roles = $this->getUserRoles();


                if (is_array($request->get('with')) && in_array('participantStatus', $request->get('with'))) {
                    $testTakes->with('testParticipants', 'testParticipants.testTakeStatus');
                    $testTakeTaken = [];
                    $testTakeNotTaken = [];
                } elseif (is_array($request->get('with')) && in_array('ownTestParticipant', $request->get('with'))) {
                    $testTakes->with(['testParticipants' => function ($query) use ($userId) {
                            $query->where('user_id', $userId);
                        }, 'testParticipants.testTakeStatus']);
                }

                $testTakes = $this->filterIfNeededForDemo($testTakes->paginate(15), true);
                $testTakeSchoolClasses = [];
                $ownTestParticipants = [];
                foreach ($testTakes as $i => $testTake) {
                    $testTakeSchoolClasses[$i] = $testTake->schoolClasses()->get();

                    if (is_array($request->get('with')) && in_array('participantStatus', $request->get('with'))) {
                        $testTakeTaken[$i] = 0;
                        $testTakeNotTaken[$i] = 0;
                        foreach ($testTake->testParticipants as $testParticipant) {
                            if (in_array($testParticipant->testTakeStatus->getAttribute('name'), ['Handed in', 'Taken away', 'Taken', 'Discussing', 'Discussed'])) {
                                $testTakeTaken[$i] ++;
                            } else {
                                $testTakeNotTaken[$i] ++;
                            }
                        }
                    } elseif (is_array($request->get('with')) && in_array('ownTestParticipant', $request->get('with'))) {
                        foreach ($testTake->testParticipants as $testParticipant) {
                            if ($testParticipant->getAttribute('user_id') == $userId) {
                                $ownTestParticipants[$i] = $testParticipant;
                            }
                        }
                    }
                }

                $testTakes = $testTakes->toArray();
                foreach ($testTakes['data'] as $i => $testTake) {
                    $testTakes['data'][$i]['school_classes'] = $testTakeSchoolClasses[$i];

                    if (array_key_exists($i, $ownTestParticipants)) {
                        $testTakes['data'][$i]['test_participant'] = $ownTestParticipants[$i];
                    }

                    if (is_array($request->get('with')) && in_array('participantStatus', $request->get('with'))) {
                        $testTakes['data'][$i]['participants_taken'] = $testTakeTaken[$i];
                        $testTakes['data'][$i]['participants_not_taken'] = $testTakeNotTaken[$i];
                    }

                    if (is_array($request->get('with')) && (in_array('participantStatus', $request->get('with')) || in_array('ownTestParticipant', $request->get('with')))) {
                        unset($testTakes['data'][$i]['test_participants']);
                    }
                }
                return Response::make($testTakes, 200);
                break;
        }
    }

    /**
     * Store a newly created test take in storage.
     *
     * @param CreateTestTakeRequest $request
     * @return Response
     */
    public function store(CreateTestTakeRequest $request) {
        $testTake = new TestTake();
        $testTake->fill($request->all());
        $testTake->setAttribute('user_id', Auth::id());
        if ($testTake->save() !== false) {
            return Response::make($testTake, 200);
        } else {
            return Response::make('Failed to create test take', 500);
        }
    }

    /**
     * Display the specified test take.
     *
     * @param TestTake $testTake
     * @return Response
     */
    public function show(TestTake $testTake, Request $request) {
        $testTake->load([
            'test',
            'test.subject' => function ($query) {
                $query->withTrashed();
            },
            'test.author',
            'retakeTestTake',
            'user',
            'testTakeStatus',
            'invigilatorUsers',
            'testParticipants',
            'testTakeCode'
        ]);

        $isInvigilator = false;
        $roles = $this->getUserRoles();

        if (in_array('Teacher', $roles) || in_array('Invigilator', $roles)) {
            $isInvigilator = $testTake->isAllowedToView(Auth::user());
            if (!$isInvigilator) {
                return Response::make([], 403);
            }
        }

        $ownTestParticipant = null;
        if (in_array('Student', $roles)) {
            foreach ($testTake->testParticipants as $user) {
                if ($user->getAttribute('user_id') == Auth::id()) {
                    $ownTestParticipant = $user;
                    break;
                }
            }
        }

        if ($isInvigilator && is_array($request->get('with')) && in_array('participantStatus', $request->get('with'))) {
            if ($testTake->testTakeStatus->name == 'Taking test') {
                $testTake->load('test.testQuestions.question', 'testParticipants.schoolClass', 'testParticipants.schoolClass.schoolLocation', 'testParticipants.schoolClass.schoolLocation.schoolLocationIps', 'testParticipants', 'testParticipants.user', 'testParticipants.testTakeEvents', 'testParticipants.testTakeEvents.testTakeEventType', 'testParticipants.testTakeStatus', 'testParticipants.answers');

                // Calculate max score
                $questionMaxScore = 0;
                $pointsPerQuestion = [];
                foreach ($testTake->test->testQuestions as $testQuestion) {
                    if ($testQuestion->question instanceof GroupQuestion) {
                        $testQuestion->question->getQuestionScores([], $questionMaxScore, $pointsPerQuestion);
                    } else {
                        $questionMaxScore += $testQuestion->question->getAttribute('score');
                        $pointsPerQuestion[$testQuestion->question->getKey()] = $testQuestion->question->getAttribute('score');
                    }
                }

                // Cleanup test relations
                $testRelations = $testTake->test->getRelations();
                unset($testRelations['questions']);
                unset($testRelations['questionGroups']);
                $testTake->test->setRelations($testRelations);

                // Calculate allowed heartbeat date
                $heartbeatDate = Carbon::now();
                $heartbeatDate->subSeconds(30);

                $schoolClasses = [];
                $alertsCount = 0;

                foreach ($testTake->testParticipants as $testParticipant) {

                    $testParticipantRelations = $testParticipant->getRelations();

                    $testParticipant->setAttribute('max_score', $questionMaxScore);

                    if ($testParticipant->getAttribute('heartbeat_at') !== null && $testParticipant->getAttribute('heartbeat_at') >= $heartbeatDate) {
                        $testParticipant->setAttribute('active', true);
                    } else {
                        $testParticipant->setAttribute('active', false);
                    }

                    $madeScore = 0;
                    foreach ($testParticipant->answers as $answer) {
                        $questionId = $answer->getAttribute('question_id');
                        if (array_key_exists($questionId, $pointsPerQuestion) && $answer->getAttribute('done') == 1) {
                            $madeScore += $pointsPerQuestion[$questionId];
                        }
                    }
                    $testParticipant->setAttribute('made_score', $madeScore);

                    $alert = $this->getAlertStatusOrParticipant($testParticipant);
                    if ($alert === true) {
                        $alertsCount++;
                    }

                    $testParticipant->setAttribute('alert', $alert);

                    if (!array_key_exists($testParticipant->schoolClass->getKey(), $schoolClasses)) {
                        $schoolClasses[$testParticipant->schoolClass->getKey()] = $testParticipant->schoolClass;
                        $schoolClasses[$testParticipant->schoolClass->getKey()]->setAttribute('made_score', 0);
                        $schoolClasses[$testParticipant->schoolClass->getKey()]->setAttribute('max_score', 0);
                    }

                    if (in_array($testParticipant->testTakeStatus->getAttribute('name'), ['Taking test', 'Handed in', 'Taken'])) {
                        $schoolMadeScore = $schoolClasses[$testParticipant->schoolClass->getKey()]->getAttribute('made_score');
                        $schoolMaxScore = $schoolClasses[$testParticipant->schoolClass->getKey()]->getAttribute('max_score');
                        $schoolMadeScore += $madeScore;
                        $schoolMaxScore += $questionMaxScore;
                        $schoolClasses[$testParticipant->schoolClass->getKey()]->setAttribute('made_score', $schoolMadeScore);
                        $schoolClasses[$testParticipant->schoolClass->getKey()]->setAttribute('max_score', $schoolMaxScore);
                    }

                    $ipCorrect = null;

                    foreach ($testParticipant->schoolClass->schoolLocation->schoolLocationIps as $schoolLocationIp) {
                        if ($schoolLocationIp->ipInRange($testParticipant->getIpAddressInBinary())) {
                            $ipCorrect = true;
                            break;
                        } elseif ($ipCorrect === null) {
                            $ipCorrect = false;
                        }
                    }

                    if ($ipCorrect === null) {
                        $ipCorrect = true;
                    }

                    $testParticipant->setAttribute('ip_correct', $ipCorrect);
                    //testParticipants.schoolClass.schoolLocation', 'testParticipants.schoolClass.schoolLocation.schoolLocationIps

                    if (!in_array('answers', $request->get('with'))) {
                        unset($testParticipantRelations['answers']);
                    }

                    $testParticipant->setRelations($testParticipantRelations);
                }

                // REMOVE in_array('answers', $request->get('with')) when fase B is done
                if ($isInvigilator && is_array($request->get('with')) && in_array('answers', $request->get('with')) && !in_array('participantStatus', $request->get('with'))) {
                    $testTake->load('testParticipants', 'testParticipants.schoolClass', 'testParticipants.schoolClass.schoolLocation', 'testParticipants.user', 'testParticipants.testTakeEvents', 'testParticipants.testTakeEvents.testTakeEventType', 'testParticipants.testTakeStatus', 'testParticipants.answers');
                }

                $testTake->setAttribute('alerts', $alertsCount);

                $testTake = $testTake->toArray();

                $testTake['school_classes'] = array_values($schoolClasses);

                return Response::make($testTake, 200);
            } elseif ($testTake->testTakeStatus->name == 'Discussing') {
                $testTake->load(['discussingParentQuestions' => function ($query) {
                        $query->orderBy('level');
                    }, 'testParticipants', 'testParticipants.testTakeStatus', 'testParticipants.user', 'testParticipants.answers', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                        $query->orderBy('level');
                    }, 'testParticipants.answers.answerRatings' => function ($query) use ($testTake) {
                        $query->where('test_take_id', $testTake->getKey());
                    }]);

                $questionId = $testTake->getAttribute('discussing_question_id');
                if ($questionId != null) {
                    $testTake->setAttribute('discussing_question_uuid', Question::find($questionId)->uuid);
                }

                $parents = null;
                foreach ($testTake->discussingParentQuestions as $discussingParentQuestions) {
                    if ($parents !== null) {
                        $parents .= '.';
                    }
                    $parents .= $discussingParentQuestions->getAttribute('group_question_id');
                }

                $testParticipantUserIds = $testTake->testParticipants->pluck('id', 'user_id')->all();
                $activeAnswerRatingsPerTestParticipant = [];
                $ratedAnswerRatingsPerTestParticipant = [];
                $testParticipantAbnormalities = [];
                $schoolClasses = [];

                $heartbeatDate = Carbon::now();
                $heartbeatDate->subSeconds(30);

                foreach ($testTake->testParticipants as $testParticipant) {
                    if ($testParticipant->getAttribute('heartbeat_at') !== null && $testParticipant->getAttribute('heartbeat_at') >= $heartbeatDate) {
                        $testParticipant->setAttribute('active', true);
                    } else {
                        $testParticipant->setAttribute('active', false);
                    }

                    if (!array_key_exists($testParticipant->schoolClass->getKey(), $schoolClasses)) {
                        $schoolClasses[$testParticipant->schoolClass->getKey()] = $testParticipant->schoolClass;
                    }

                    foreach ($testParticipant->answers as $answer) {
                        // Decide if this is question that is currently being discussed
                        $answerParents = null;
                        foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                            if ($answerParents !== null) {
                                $answerParents .= '.';
                            }
                            $answerParents .= $answerParentQuestion->getAttribute('group_question_id');
                        }

                        $teacherRating = null;
                        $systemRating = null;
                        $studentRatings = [];
                        foreach ($answer->answerRatings as $answerRating) {

                            if ($answer->getAttribute('question_id') == $questionId && $parents == $answerParents) {
                                if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
                                    $testParticipantId = $testParticipantUserIds[$answerRating->getAttribute('user_id')];

                                    if (!array_key_exists($testParticipantId, $activeAnswerRatingsPerTestParticipant)) {
                                        $activeAnswerRatingsPerTestParticipant[$testParticipantId] = 0;
                                    }

                                    if (!array_key_exists($testParticipantId, $ratedAnswerRatingsPerTestParticipant)) {
                                        $ratedAnswerRatingsPerTestParticipant[$testParticipantId] = 0;
                                    }

                                    $activeAnswerRatingsPerTestParticipant[$testParticipantId] ++;
                                    if ($answerRating->getAttribute('rating') != null) {
                                        $ratedAnswerRatingsPerTestParticipant[$testParticipantId] ++;
                                    }
                                }
                            } elseif ($answerRating->getAttribute('type') === 'STUDENT' && $answerRating->getAttribute('rating') != null) {
                                $studentRatings[] = $answerRating->getAttribute('rating');
                            } elseif ($answerRating->getAttribute('type') === 'SYSTEM') {
                                $systemRating = $answerRating->getAttribute('rating');
                            } elseif ($answerRating->getAttribute('type') === 'TEACHER') {
                                $teacherRating = $answerRating->getAttribute('rating');
                            }
                        }

                        $studentRatings = array_unique($studentRatings);
                        if ($teacherRating !== null || $systemRating !== null) {
                            $wantedRating = ($teacherRating !== null) ? $teacherRating : $systemRating;
                            foreach ($answer->answerRatings as $answerRating) {
                                if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
                                    $testParticipantId = $testParticipantUserIds[$answerRating->getAttribute('user_id')];
                                    if ($answerRating->getAttribute('type') === 'STUDENT' && $answerRating->getAttribute('rating') != $wantedRating && array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
                                        if (!array_key_exists($testParticipantId, $testParticipantAbnormalities)) {
                                            $testParticipantAbnormalities[$testParticipantId] = 0;
                                        }
                                        $testParticipantAbnormalities[$testParticipantId] ++;
                                    }
                                }
                            }
                        } elseif (count($studentRatings) > 1) {
                            foreach ($answer->answerRatings as $answerRating) {
                                if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
                                    $testParticipantId = $testParticipantUserIds[$answerRating->getAttribute('user_id')];
                                    if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
                                        if (!array_key_exists($testParticipantId, $testParticipantAbnormalities)) {
                                            $testParticipantAbnormalities[$testParticipantId] = 0;
                                        }
                                        $testParticipantAbnormalities[$testParticipantId] ++;
                                    }
                                }
                            }
                        }
                    }

                    $relations = $testParticipant->getRelations();
                    unset($relations['answers']);
                    //clear up loaded relations
                    $testParticipant->setRelations($relations);
                }

                foreach ($testTake->testParticipants as $testParticipant) {
                    if (array_key_exists($testParticipant->getKey(), $activeAnswerRatingsPerTestParticipant)) {
                        $testParticipant->setAttribute('answer_to_rate', $activeAnswerRatingsPerTestParticipant[$testParticipant->getKey()]);
                    }

                    if (array_key_exists($testParticipant->getKey(), $ratedAnswerRatingsPerTestParticipant)) {
                        $testParticipant->setAttribute('answer_rated', $ratedAnswerRatingsPerTestParticipant[$testParticipant->getKey()]);
                    }

                    if (array_key_exists($testParticipant->getKey(), $testParticipantAbnormalities)) {
                        $testParticipant->setAttribute('abnormalities', $testParticipantAbnormalities[$testParticipant->getKey()]);
                    } elseif (array_key_exists($testParticipant->getKey(), $activeAnswerRatingsPerTestParticipant) || array_key_exists($testParticipant->getKey(), $ratedAnswerRatingsPerTestParticipant)) {
                        $testParticipant->setAttribute('abnormalities', 0);
                    }
                }

                $testTake = $testTake->toArray();
                $testTake['school_classes'] = array_values($schoolClasses);
                return Response::make($testTake, 200);
            }
        } elseif ($isInvigilator && is_array($request->get('with')) && in_array('scores', $request->get('with'))) {
            $ratings = [];

            foreach ($testTake->testParticipants as $testParticipant) {
                $retakeRating = $testParticipant->getAttribute('retake_rating');
                $rating = $testParticipant->getAttribute('rating');
                if (!empty($retakeRating)) {
                    $ratings[] = $retakeRating;
                } elseif ($rating) {
                    $ratings[] = $rating;
                }
            }

            $testTake->setAttribute('max_rating', max($ratings));
            $testTake->setAttribute('avg_rating', (count($ratings) > 0) ? array_sum($ratings) / count($ratings) : 0);
            $testTake->setAttribute('mix_rating', min($ratings));

            $testTakeStatusesRetake = TestTakeStatus::whereIn('name', ['Planned', 'Test not taken'])->pluck('id');

            $testTake->load(['testParticipants' => function ($query) use ($testTakeStatusesRetake) {
                    $query->select(['id', 'created_at', 'updated_at', 'deleted_at', 'user_id', 'rating', 'retake_rating']);
                }]);

            return Response::make($testTake, 200);
        } elseif ($isInvigilator && is_array($request->get('with')) && in_array('averages', $request->get('with'))) {
            $testTake->load(['testParticipants.answers', 'testParticipants.answers.answerRatings', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                    $query->orderBy('level');
                }]);
            $questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);

            foreach ($questions as $answerQuestionId => $question) {
                $averageScore = $questions[$answerQuestionId]->getAttribute('total_score');
                $ratings = $questions[$answerQuestionId]->getAttribute('ratings');
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                foreach ($testParticipant->answers as $answer) {
                    $answerQuestionId = null;
                    foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                        if ($answerQuestionId !== null) {
                            $answerQuestionId .= '.';
                        }
                        $answerQuestionId .= $answerParentQuestion->getAttribute('group_question_id');
                    }

                    if ($answerQuestionId !== null) {
                        $answerQuestionId .= '.';
                    }

                    $answerQuestionId .= $answer->getAttribute('question_id');

                    if (array_key_exists($answerQuestionId, $questions)) {
                        $answerScore = $answer->getAttribute('final_rating');

                        if ($answerScore === null) {
                            $answerScore = $answer->calculateFinalRating();
                            if ($answerScore !== null) {
                                $answer->setAttribute('final_rating', $answerScore);
                                $answer->save();
                            }
                        }

                        if ($answerScore !== null) {
                            $averageScore = $questions[$answerQuestionId]->getAttribute('total_score');
                            $ratings = $questions[$answerQuestionId]->getAttribute('ratings');
                            $averageScore += $answerScore;
                            $ratings++;
                            $questions[$answerQuestionId]->setAttribute('total_score', $averageScore);
                            $questions[$answerQuestionId]->setAttribute('ratings', $ratings);
                        }
                    }
                }
            }

            $testTake = $testTake->toArray();
            unset($testTake['test_participants']);
            $testTake['questions'] = $questions;

            return Response::make($testTake, 200);
        } elseif ($isInvigilator && $testTake->testTakeStatus->name == 'Discussing') {
            $testTake->load(['discussingParentQuestions' => function ($query) {
                    $query->orderBy('level');
                }]);
            if ($testTake->getAttribute('discussing_question_id') != null) {
                $testTake->setAttribute('discussing_question_uuid', Question::find($testTake->getAttribute('discussing_question_id'))->uuid);
            }
        }

        $schoolClasses = $testTake->schoolClasses()->orderBy('name')->get();
        $test = $testTake->test;
        $testTake = $testTake->toArray();

        if ($ownTestParticipant !== null) {
            $testTake['test_participant'] = $ownTestParticipant;
        }
        $testTake['has_active_participants'] = !!(collect($testTake['test_participants'])->where('test_take_status_id', '>', 2)->count() > 0);
        unset($testTake['test_participants']);

        $testTake['school_classes'] = $schoolClasses;

        $testTake['consists_only_closed_question'] = $test->hasOpenQuestion() ? false : true;

        return Response::make($testTake, 200);
    }

    public function nextQuestion(TestTake $testTake) {
        if ($testTake->testTakeStatus->name == 'Discussing') {
            $testTake->load(['discussingParentQuestions' => function ($query) {
                    $query->orderBy('level');
                }, 'testParticipants', 'testParticipants.answers', 'testParticipants.answers.answerRatings' => function ($query) {
                    $query->where('type', 'STUDENT');
                }]);

            // Set next question
            $questionId = null;
            foreach ($testTake->discussingParentQuestions as $discussingParentQuestions) {
                if ($questionId !== null) {
                    $questionId .= '.';
                }
                $questionId .= $discussingParentQuestions->getAttribute('group_question_id');
            }

            if ($questionId !== null) {
                $questionId .= '.';
            }

            $questionId .= $testTake->getAttribute('discussing_question_id');

            $newQuestionIdParents = QuestionGatherer::getNextQuestionId($testTake->getAttribute('test_id'), $questionId, in_array($testTake->getAttribute('discussion_type'), ['OPEN_ONLY']));

            $testTake->discussingParentQuestions()->delete();
            if ($newQuestionIdParents === false) {
                $testTake->setAttribute('discussing_question_id', null);
                if (!$testTake->save()) {
                    return Response::make('Failed to update test take', 500);
                }
            } else {
                $newQuestionIdParentParts = explode('.', $newQuestionIdParents);
                $newQuestionId = array_pop($newQuestionIdParentParts);
                $discuss = QuestionGatherer::getQuestionOfTest($testTake->getAttribute('test_id'), $newQuestionIdParents, true);
                $discuss = ($discuss instanceof Question) ? $discuss->getAttribute('discuss') == true : false;
                $testTake->setAttribute('discussing_question_id', $newQuestionId);

                $level = 1;

                $discussingParentQuestions = [];
                foreach ($newQuestionIdParentParts as $newQuestionIdParent) {
                    $discussingParentQuestion = new DiscussingParentQuestion();
                    $discussingParentQuestion->setAttribute('level', $level);
                    $discussingParentQuestion->setAttribute('group_question_id', $newQuestionIdParent);
                    $discussingParentQuestions[] = $discussingParentQuestion;
                }

                $testTake->discussingParentQuestions()->saveMany($discussingParentQuestions);
                if (!$testTake->save()) {
                    return Response::make('Failed to update test take', 500);
                }

                $testTake->setAttribute('has_next_question', (QuestionGatherer::getNextQuestionId($testTake->getAttribute('test_id'), $newQuestionIdParents, in_array($testTake->getAttribute('discussion_type'), ['OPEN_ONLY'])) !== false));

                // Generate for active students next answer_ratings
                if ($discuss) {
                    $parents = implode('.', $newQuestionIdParentParts);
                    $answerToRate = [];
                    $testParticipantUserIds = [];
                    $testParticipants = [];

                    foreach ($testTake->testParticipants as $testParticipant) {
                        foreach ($testParticipant->answers as $answer) {
                            //Log::debug($answer);
                            // Decide if this is question that is currently being discussed
                            if ($answer->getAttribute('question_id') == $newQuestionId) {
                                $answerParents = null;
                                foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                                    if ($answerParents !== null) {
                                        $answerParents .= '.';
                                    }
                                    $answerParents .= $answerParentQuestion->getAttribute('group_question_id');
                                }

                                if ($parents == $answerParents && $answer->answerRatings->isEmpty()) {
                                    $answerToRate[$testParticipant->getKey()] = $answer;
                                    $testParticipantUserIds[$testParticipant->getKey()] = $testParticipant->getAttribute('user_id');
                                    $testParticipants[$testParticipant->getKey()] = $testParticipant;
                                }
                            }
                        }
                    }

                    $shuffledTestParticipants = array_keys($answerToRate);
                    shuffle($shuffledTestParticipants);
                    $shuffledAnswers = array();
                    foreach ($shuffledTestParticipants as $shuffledTestParticipant) {
                        $shuffledAnswers[] = $answerToRate[$shuffledTestParticipant];
                    }
                    $shuffledAnswers = array_combine($shuffledTestParticipants, $shuffledAnswers);

                    $answerPerTestParticipant = count($answerToRate);
                    if ($answerPerTestParticipant > 2) {
                        $answerPerTestParticipant = 2;
                    }

                    $firstAssignedAnswers = [];
                    for ($i = 0; $i < $answerPerTestParticipant; $i++) {
                        $values = array_values($shuffledAnswers);
                        array_push($values, array_shift($values));
                        $shuffledAnswers = array_combine(array_keys($shuffledAnswers), $values);

                        foreach ($shuffledAnswers as $testParticipant => $answer) {
                            if ($answer->getAttribute('json') === null) {
                                continue;
                            }

                            $answerRating = new AnswerRating();
                            $answerRating->setAttribute('answer_id', $answer->getKey());
                            $answerRating->setAttribute('user_id', $testParticipantUserIds[$testParticipant]);
                            $answerRating->setAttribute('test_take_id', $testTake->getKey());
                            $answerRating->setAttribute('type', 'STUDENT');

                            $answerRating->save();

                            if (!array_key_exists($testParticipant, $firstAssignedAnswers)) {
                                $firstAssignedAnswers[$testParticipant] = $answer->getKey();
                            }
                        }
                    }

                    foreach ($testParticipants as $testParticipantId => $testParticipant) {
                        if (array_key_exists($testParticipantId, $firstAssignedAnswers)) {
                            $testParticipant->setAttribute('answer_id', $answer->getKey());
                            $testParticipant->save();
                        } else {
                            $testParticipant->setAttribute('answer_id', null);
                            $testParticipant->save();
                        }
                    }
                }
            }


            return Response::make($testTake, 200);
        } else {
            return Response::make('Failed to set next question, test take is not being discussed', 500);
        }
    }

    public function normalize(TestTake $testTake, NormalizeTestTakeRequest $request)
    {
        $testTake->load(['testParticipants', 'testParticipants.user', 'testParticipants.answers', 'testParticipants.answers.answerRatings', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                $query->orderBy('level');
            }]);
        $ignoreQuestions = $request->get('ignore_questions');



        if ($request->filled('ppp') || $request->filled('epp') || $request->filled('wanted_average') || $request->filled('n_term')) {
            $testTake->setAttribute('ppp', null);
            $testTake->setAttribute('epp', null);
            $testTake->setAttribute('wanted_average', null);
            $testTake->setAttribute('n_term', null);
            $testTake->setAttribute('pass_mark', null);
        }

        $questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);
        if (
            (
                (
                    !$request->filled('ppp')
                    && $testTake->getAttribute('ppp') === null
                )
                && (
                    $request->filled('epp')
                    || $testTake->getAttribute('epp')
                )
            )
            || (
                (
                    !$request->filled('ppp')
                    && $testTake->getAttribute('ppp') === null
                    && !$request->filled('epp')
                    && $testTake->getAttribute('epp') === null
                    && !$request->filled('wanted_average')
                    && $testTake->getAttribute('wanted_average') === null
                )
                && (
                    $request->filled('n_term')
                    || (
                        $testTake->getAttribute('n_term') !== null
                    )
                )
            )
        ) {
            // $totalScore = 0;
            // foreach ($questions as $questionId => $question) {
            //     if ($ignoreQuestions === null || !in_array($questionId, $ignoreQuestions)) {
            //         $totalScore += $question->getAttribute('score');
            //     }
            // }
            $totalScore = $this->maxScore($testTake,$ignoreQuestions);
        } else {
            $totalScore = null;
        }
        $scores = [];
        foreach ($testTake->testParticipants as $testParticipant) {
            $score = 0;

            foreach ($testParticipant->answers as $answer) {
                $answerQuestionId = null;
                foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                    if ($answerQuestionId !== null) {
                        $answerQuestionId .= '.';
                    }
                    $answerQuestionId .= $answerParentQuestion->getAttribute('group_question_id');
                }

                if ($answerQuestionId !== null) {
                    $answerQuestionId .= '.';
                }
                $answerQuestionId .= $answer->getAttribute('question_id');

                if ($ignoreQuestions !== null && in_array($answerQuestionId, $ignoreQuestions)) {
                    $answer->setAttribute('ignore_for_rating', true);
                } else {
                    $answer->setAttribute('ignore_for_rating', false);
                    $answerScore = $answer->getAttribute('final_rating');

                    if ($answerScore === null) {
                        $answerScore = $answer->calculateFinalRating();
                        if ($answerScore !== null) {
                            $answer->setAttribute('final_rating', $answerScore);
                        }
                    }

                    if ($score !== false && $answerScore !== null) {
                        $score += $answerScore;
                    } else {
                        $score = false;
                    }
                }

                $answer->save();
            }

            if (!$testParticipant->answers->isEmpty() && $score !== false) {
                $scores[$testParticipant->getKey()] = $score;
            }

            $relations = $testParticipant->getRelations();
            unset($relations['answers']);
            $testParticipant->setRelations($relations);
        }

        if ($request->filled('ppp') || $testTake->getAttribute('ppp') !== null) {
            $ppp = ($request->filled('ppp')) ? $request->get('ppp') : $testTake->getAttribute('ppp');
            $testTake->setAttribute('ppp', $ppp);
            if (!$request->filled('preview') || $request->get('preview') != true) {
                $testTake->save();
            }
            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = ($score / $ppp);
                    if ($rate < 1) {
                        $rate = 1;
                    } elseif ($rate > 10) {
                        $rate = 10;
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!$request->filled('preview') || $request->get('preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif ($request->filled('epp') || $testTake->getAttribute('epp') !== null) {
            $epp = ($request->filled('epp')) ? $request->get('epp') : $testTake->getAttribute('epp');

            $testTake->setAttribute('epp', $epp);
            if (!$request->filled('preview') || $request->get('preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = 10 - (($totalScore - $score) / $epp);
                    if ($rate < 1) {
                        $rate = 1;
                    } elseif ($rate > 10) {
                        $rate = 10;
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!$request->filled('preview') || $request->get('preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif ($request->filled('wanted_average') || $testTake->getAttribute('wanted_average') !== null) {
            $average = ($request->filled('wanted_average')) ? $request->get('wanted_average') : $testTake->getAttribute('wanted_average');
            $testTake->setAttribute('wanted_average', $average);
            if (!$request->filled('preview') || $request->get('preview') != true) {
                $testTake->save();
            }
            if ($scores) {
                $ppp = ((array_sum($scores) / count($scores)) / ($average - 1));
                foreach ($testTake->testParticipants as $testParticipant) {
                    if (array_key_exists($testParticipant->getKey(), $scores)) {
                        $score = $scores[$testParticipant->getKey()];
                        $rate = 1 + ($score / $ppp);
                        if ($rate < 1) {
                            $rate = 1;
                        } elseif ($rate > 10) {
                            $rate = 10;
                        }

                        $testParticipant->setAttribute('rating', round($rate, 1));
                        if (!$request->filled('preview') || $request->get('preview') != true) {
                            $testParticipant->save();
                        }

                        $testParticipant->setAttribute('score', $score);
                    }
                }
            }
        } elseif ($request->filled('n_term') && $request->filled('pass_mark') || ($testTake->getAttribute('n_term') !== null && $testTake->getAttribute('pass_mark') !== null)) {
            $nTerm = ($request->filled('n_term')) ? $request->get('n_term') : $testTake->getAttribute('n_term');
            $passMark = ($request->filled('pass_mark')) ? $request->get('pass_mark') : $testTake->getAttribute('pass_mark');
            $testTake->setAttribute('n_term', $nTerm);
            $testTake->setAttribute('pass_mark', $passMark);
            if (!$request->filled('preview') || $request->get('preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    if (($score / $totalScore) < ($passMark / 100)) {
                        if ($passMark <= 0) {
                            $rate = 10;
                        } elseif ($nTerm <= -3.5) {
                            $rate = 1;
                        } else {
                            $rate = 1 + $score / (($totalScore * ($passMark / 100)) / (4.5 + ($nTerm - 1)));
                        }
                    } elseif (($score / $totalScore) > ($passMark / 100)) {
                        if ($passMark >= 100) {
                            $rate = 1;
                        } elseif ($nTerm >= 5.5) {
                            $rate = 10;
                        } else {
                            $rate = 10 - (($totalScore - $score) * ((4.5 - ($nTerm - 1)) / ($totalScore - ($totalScore * ($passMark / 100)))));
                        }
                    } else {
                        $rate = (5.5 + $nTerm - 1);
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!$request->filled('preview') || $request->get('preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        } elseif ($request->filled('n_term') || $testTake->getAttribute('n_term') !== null) {
            $nTerm = ($request->filled('n_term')) ? $request->get('n_term') : $testTake->getAttribute('n_term');

            $testTake->setAttribute('n_term', $nTerm);
            if (!$request->filled('preview') || $request->get('preview') != true) {
                $testTake->save();
            }

            foreach ($testTake->testParticipants as $testParticipant) {
                if (array_key_exists($testParticipant->getKey(), $scores)) {
                    $score = $scores[$testParticipant->getKey()];
                    $rate = (9.0 * ($score / $totalScore)) + $nTerm;

                    if ($nTerm > 1) {
                        $scoreMin = 1.0 + ($score * (9 / $totalScore) * 2);
                        $scoreMax = 10 - (($totalScore - $score) * (9 / $totalScore) * 0.5);

                        if ($scoreMin < $scoreMax && $rate > $scoreMin) {
                            $rate = $scoreMin;
                        } elseif ($scoreMin > $scoreMax && $rate > $scoreMax) {
                            $rate = $scoreMax;
                        }
                    } elseif ($nTerm < 1) {
                        $scoreMin = 1.0 + ($score * (9 / $totalScore) * 0.5);
                        $scoreMax = 10 - (($totalScore - $score) * (9 / $totalScore) * 2);

                        if ($scoreMin > $scoreMax && $rate < $scoreMin) {
                            $rate = $scoreMin;
                        } elseif ($scoreMin < $scoreMax && $rate < $scoreMax) {
                            $rate = $scoreMax;
                        }
                    } else {
                        $scoreMin = 1;
                        $scoreMax = 10;

                        if ($rate < $scoreMin) {
                            $rate = $scoreMin;
                        } elseif ($rate > $scoreMax) {
                            $rate = $scoreMax;
                        }
                    }

                    $testParticipant->setAttribute('rating', round($rate, 1));
                    if (!$request->filled('preview') || $request->get('preview') != true) {
                        $testParticipant->save();
                    }

                    $testParticipant->setAttribute('score', $score);
                }
            }
        }

        return Response::make($testTake, 200);
    }

    public function export(TestTake $testTake)
    {
        $questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);

        $testTake->load(['testParticipants', 'testParticipants.user', 'testParticipants.answers' => function ($query) {
                $query->select(['id', 'created_at', 'updated_at', 'deleted_at', 'test_participant_id', 'question_id', 'order', 'time', 'done', 'final_rating', 'ignore_for_rating']);
            }, 'testParticipants.answers.answerRatings', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                $query->orderBy('level');
            }]);

        $sheet = [];

        $testParticipantsAnswers = [];
        $questionsNotIgnored = [];

        foreach ($testTake->testParticipants as $testParticipant) {
            foreach ($testParticipant->answers as $answer) {
                if ($answer->getAttribute('ignore_for_rating')) {
                    continue;
                }

                $answerDottedId = null;
                foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                    if ($answerDottedId !== null) {
                        $answerDottedId .= '.';
                    }
                    $answerDottedId .= $answerParentQuestion->getAttribute('group_question_id');
                }

                if ($answerDottedId !== null) {
                    $answerDottedId .= '.';
                }
                $answerDottedId .= $answer->getAttribute('question_id');

                $testParticipantsAnswers[$testParticipant->getKey()][$answerDottedId] = $answer;
                if (!in_array($answerDottedId, $questionsNotIgnored)) {
                    $questionsNotIgnored[] = $answerDottedId;
                }
            }
        }

        $header = ['Studentnummer', 'Voornaam', 'Tussenvoegsels', 'Achternaam', 'Cijfer', 'Score', 'R-score', 'T1-score', 'T2-score', 'I-score', '?-score'];
        $questionNumber = 0;
        $scoreMax = 0;
        $rScoreMax = 0;
        $t1ScoreMax = 0;
        $t2ScoreMax = 0;
        $iScoreMax = 0;
        $nScoreMax = 0;
        foreach ($questions as $questionDottedId => $question) {
            $questionNumber++;

            if (!in_array($questionDottedId, $questionsNotIgnored)) {
                continue;
            }
            $scoreMax += $question->getAttribute('score');

            switch ($question->getAttribute('rtti')) {
                case 'R':
                    $rScoreMax += $question->getAttribute('score');
                    break;
                case 'T1':
                    $t1ScoreMax += $question->getAttribute('score');
                    break;
                case 'T2':
                    $t2ScoreMax += $question->getAttribute('score');
                    break;
                case 'I':
                    $iScoreMax += $question->getAttribute('score');
                    break;
                default:
                    $nScoreMax += $question->getAttribute('score');
                    break;
            }

            if ($question->getAttribute('rtti') === null) {
                $header[] = $questionNumber . ' (?)';
            } else {
                $header[] = $questionNumber . ' (' . $question->getAttribute('rtti') . ')';
            }
        }
        $sheet[] = $header;

        foreach ($testTake->testParticipants as $testParticipant) {
            $score = 0;
            $rScore = 0;
            $t1Score = 0;
            $t2Score = 0;
            $iScore = 0;
            $nScore = 0;
            $row = [];

            foreach ($questions as $questionDottedId => $question) {
                if (!in_array($questionDottedId, $questionsNotIgnored)) {
                    continue;
                }

                if (array_key_exists($testParticipant->getKey(), $testParticipantsAnswers) && array_key_exists($questionDottedId, $testParticipantsAnswers[$testParticipant->getKey()])) {
                    $answer = $testParticipantsAnswers[$testParticipant->getKey()][$questionDottedId];

                    $answerScore = $answer->getAttribute('final_rating');

                    if ($answerScore === null) {
                        $answerScore = $answer->calculateFinalRating();
                        if ($answerScore !== null) {
                            $answer->setAttribute('final_rating', $answerScore);
                        }
                    }

                    $row[] = $answerScore;
                    $score += $answerScore;
                    switch ($question->getAttribute('rtti')) {
                        case 'R':
                            $rScore += $answerScore;
                            break;
                        case 'T1':
                            $t1Score += $answerScore;
                            break;
                        case 'T2':
                            $t2Score += $answerScore;
                            break;
                        case 'I':
                            $iScore += $answerScore;
                            break;
                        default:
                            $nScore += $answerScore;
                            break;
                    }
                } else {
                    $row[] = '-';
                }
            }

            if ($rScoreMax === 0) {
                $rScore = '-';
            } else {
                $rScore = (float) $rScore;
                $rScore /= (float) $rScoreMax;
                $rScore *= 100;
                $rScore = round($rScore);
            }

            if ($t1ScoreMax === 0) {
                $t1Score = '-';
            } else {
                $t1Score = (float) $t1Score;
                $t1Score /= (float) $t1ScoreMax;
                $t1Score *= 100;
                $t1Score = round($t1Score);
            }

            if ($t2ScoreMax === 0) {
                $t2Score = '-';
            } else {
                $t2Score = (float) $t2Score;
                $t2Score /= (float) $t2ScoreMax;
                $t2Score *= 100;
                $t2Score = round($t2Score);
            }

            if ($iScoreMax === 0) {
                $iScore = '-';
            } else {
                $iScore = (float) $iScore;
                $iScore /= (float) $iScoreMax;
                $iScore *= 100;
                $iScore = round($iScore);
            }

            if ($nScoreMax === 0) {
                $nScore = '-';
            } else {
                $nScore = (float) $nScore;
                $nScore /= (float) $nScoreMax;
                $nScore *= 100;
                $nScore = round($nScore);
            }

            array_unshift(
                $row,
                $testParticipant->user->getAttribute('external_id'),
                $testParticipant->user->getAttribute('name_first'),
                $testParticipant->user->getAttribute('name_suffix'),
                $testParticipant->user->getAttribute('name'),
                $testParticipant->user->getAttribute('rating'),
                $score,
                $rScore,
                $t1Score,
                $t2Score,
                $iScore,
                $nScore
            );

            $sheet[] = $row;
        }

        $export = new TestTakesExport($sheet);

        return Excel::download($export, 'export.csv');
    }

    /**
     * Close for students with no time dispensaition.
     *
     * @param TestTake $testTake
     * @param UpdateTestTakeRequest $request
     * @return Response
     */
    public function closeNonTimeDispensation(TestTake $testTake, UpdateTestTakeRequest $request) {

        if (!isset($request['time_dispensation'])) {
            return $this->update($testTake, $request);
        }

        //
        // set all the non-time dispensation students to 'taken away'
        // unless the student has not started the test yet
        //
        $closed_students = TestParticipant::join('users', 'test_participants.user_id', '=', 'users.id')
                ->where('users.time_dispensation', 0)
                ->where('test_participants.test_take_id', $testTake->id)
                ->where('test_participants.test_take_status_id','=',3)
                ->update(['test_participants.test_take_status_id' => 6]);

        //
        //   check if there are any students left doing the test, if not close test
        //   Status 2 Test not taken status 3 Taking test
        //

        $students_still_in_test = TestParticipant::where('test_participants.test_take_id', $testTake->id)
                ->whereIn('test_participants.test_take_status_id', [3])
                ->count();

        if ($students_still_in_test == 0) {

            unset($request['time_dispensation']);

            $request['test_take_status_id'] = 6;

            $this->update($testTake,$request);

        } else {

            return Response::make($testTake, 200);

        }

    }

    /**
     * Update the specified test take in storage.
     *
     * @param TestTake $testTake
     * @param UpdateTestTakeRequest $request
     * @return Response
     */
    public function update(TestTake $testTake, UpdateTestTakeRequest $request)
    {

        if (isset($request['time_dispensation']) && $request['time_dispensation'] == true) {

            $this->closeNonTimeDispensation($testTake, $request);

        } else {

            $testTake->fill($request->all());

            if ($testTake->save() !== false) {
                $this->hydrateTestTakeWithHasNextQuestionAttribute($testTake);


                return Response::make($testTake, 200);
            } else {
                return Response::make('Failed to update test take', 500);
            }
        }
    }

    /**
     * Remove the specified test take from storage.
     *
     * @param TestTake $testTake
     * @return Response
     */
    public function destroy(TestTake $testTake)
    {
        if ($testTake->delete()) {
            return Response::make($testTake, 200);
        } else {
            return Response::make('Failed to delete test take', 500);
        }
    }

    public function maxScore(TestTake $testTake,$ignoreQuestions = []){
        foreach ($ignoreQuestions as $key=>$value){
            if(!strstr($value,'.')){
                continue;
            }
            $arr = explode('.',$value);
            $ignoreQuestions[$key] = $arr[1];
        }
        $test = $testTake->test;
        return (new TestsController())->maxScore($test,$ignoreQuestions);
    }

    private function addToMaxScore(&$maxScore,$question,$ignoreQuestions):void
    {
        if(in_array($question->getKey(), $ignoreQuestions)){
            return;
        }
        $maxScore += $question->score;
    }

    public function maxScoreResponse(TestTake $testTake){
        $maxScore = $this->maxScore($testTake);
        return Response::make($maxScore, 200);
    }

    private function hydrateTestTakeWithHasNextQuestionAttribute(TestTake $testTake) {
        $testTake->load(['discussingParentQuestions' => function ($query) {
                $query->orderBy('level');
            }, 'testParticipants', 'testParticipants.testTakeStatus', 'testParticipants.user', 'testParticipants.answers', 'testParticipants.answers.answerParentQuestions' => function ($query) {
                $query->orderBy('level');
            }, 'testParticipants.answers.answerRatings' => function ($query) use ($testTake) {
                $query->where('test_take_id', $testTake->getKey());
            }]);

        $questionId = $testTake->getAttribute('discussing_question_id');
        $parents = null;
        foreach ($testTake->discussingParentQuestions as $discussingParentQuestions) {
            if ($parents !== null) {
                $parents .= '.';
            }
            $parents .= $discussingParentQuestions->getAttribute('group_question_id');
        }

        $parentsGlue = $parents;
        if ($parents !== null) {
            $parentsGlue .= '.';
        }

        $someGluedUpVariable = $parentsGlue . $questionId;
        $newQuestionIdParents = QuestionGatherer::getNextQuestionId($testTake->getAttribute('test_id'), $someGluedUpVariable, in_array($testTake->getAttribute('discussion_type'), ['OPEN_ONLY']));
        $testTake->setAttribute('has_next_question', (QuestionGatherer::getNextQuestionId($testTake->getAttribute('test_id'), $newQuestionIdParents, in_array($testTake->getAttribute('discussion_type'), ['OPEN_ONLY'])) !== false));
    }

    protected function filterIfNeededForDemo($data, $paginate = false)
    {
        // @@ see TC-160
        // we now alwas change the setting to make it faster and don't reverse it anymore
        // as on a new server we might forget to update this setting and it doesn't do any harm to do this extra query
        try { // added for compatibility with mariadb
            \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=off';"));
        } catch (\Exception $e){}

        if (Auth::user()->isA('teacher')) {
            $demoSubject = (new DemoHelper())->getDemoSubjectForTeacher(Auth::user());
            $refId = Auth::id();
            if ($demoSubject !== null) {
                $items = ($paginate === true) ? $data->getCollection() : $data;
                $list = $items->filter(function (TestTake $tt) use ($demoSubject, $refId) {
                    if ($tt->test->subject_id === $demoSubject->getKey() && $tt->test->author_id !== $refId) {
                        return false;
                    }
                    return true;
                });
                $data = ($paginate === true) ? $data->setCollection($list) : $list;
            }
        }
//        \DB::select(\DB::raw("set session optimizer_switch='condition_fanout_filter=on';"));

        return $data;
    }

    public function archive(TestTake $testTake)
    {
        return  $testTake->archiveForUser(Auth::user());
    }

    public function unArchive(TestTake $testTake)
    {
        return $testTake->unArchiveForUser(Auth::user());
    }

    public function withTemporaryLogin(TestTake $testTake) {

        $temporaryLogin = TemporaryLogin::createWithOptionsForUser('app_details', request()->get('app_details'), auth()->user());

        return BaseHelper::createRedirectUrlWithTemporaryLoginUuid($temporaryLogin->uuid,route('student.test-take-laravel', $testTake->uuid,false));

    }

    public function hasCarouselQuestion(TestTake $testTake)
    {
        return response()->json(['has_carousel' =>  $testTake->hasCarousel()]);
    }

    public function toggleInbrowserTestingForAllParticipants(TestTake $testTake)
    {
        $allow_inbrowser_testing = $testTake->allow_inbrowser_testing;
        $testTake->setAttribute('allow_inbrowser_testing', !$allow_inbrowser_testing)->save();
    }

    public function isAllowedInbrowserTesting(TestTake $testTake)
    {
        $response['allowed'] = $testTake->allow_inbrowser_testing;
        $response['guests'] = $testTake->guest_accounts;
        return $response;
    }

    private function getTestTakeSchoolClass($className, $testTake)
    {
        return [
            'schoolClass' => $className,
            'test'        => $testTake->test->name,
            'uuid'        => $testTake->uuid,
            'code'        => $testTake->testTakeCode != null ? $testTake->testTakeCode->prefix . $testTake->testTakeCode->code : ''
        ];
    }
}
