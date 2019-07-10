<?php namespace tcCore\Http\Controllers\TestTakes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\AnswerRating;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestParticipantRequest;
use tcCore\Http\Requests\HeartbeatTestParticipantRequest;
use tcCore\Http\Requests\UpdateTestParticipantRequest;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Lib\TestParticipant\Factory;
use tcCore\TestParticipant;
use tcCore\TestTake;

class TestParticipantsController extends Controller {

	/**
	 * Display a listing of the test participants.
	 *
	 * @return Response
	 */
	public function index(TestTake $testTake, Request $request)
	{
		$testParticipants = $testTake->testParticipants()->with('user', 'testTakeStatus', 'schoolClass');
		$userRoles = $this->getUserRoles();

		$isTeacherOrInvigilator = false;
		if (in_array('Teacher', $userRoles) || in_array('Invigilator', $userRoles)) {
			$isTeacherOrInvigilator = true;
		}

		if ($isTeacherOrInvigilator === false && in_array('Student', $userRoles)) {
			$testParticipants->where('user_id', '=', Auth::id());
		}

		switch(strtolower($request->get('mode', 'paginate'))) {
			case 'all':
				if ($isTeacherOrInvigilator && is_array($request->get('with')) && in_array('statistics', $request->get('with'))) {
					$testParticipants->with('answers');
				}

				$testParticipants->with(['answers', 'answers.answerParentQuestions' => function ($query) {
					$query->orderBy('level');
				}, 'answers.answerRatings' => function ($query) use ($testTake) {
					$query->where('test_take_id', $testTake->getKey());
				}]);

				$testParticipants = $testParticipants->get();
				if ($isTeacherOrInvigilator && is_array($request->get('with')) && in_array('statistics', $request->get('with'))) {
					$testParticipantUserIds = $testParticipants->pluck('id', 'user_id')->all();
					$userHasRated = AnswerRating::where('type', 'STUDENT')->where('test_take_id', $testTake->getKey())->whereNotNull('rating')->distinct()->pluck('user_id')->all();
					$testParticipantAbnormalities = [];

					$questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);

					foreach ($questions as $questionId => $question) {
						$questions[$questionId] = $question['score'];
					}

					// Calculate Score / Max Score + total time
					foreach($testParticipants as $testParticipant) {
						$score = 0;
						$maxScore = 0;
						$questionsCount = 0;
						$totalTime = 0;
						$answerRequireRating = 0;
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
									} else {
										$answerRequireRating++;
									}
								}

								if ($answerScore !== null && $answer->getAttribute('ignore_for_rating') == 0) {
									$score += $answerScore;
								}

								if ($answer->getAttribute('ignore_for_rating') == 0) {
									$maxScore += $questions[$answerQuestionId];
								}
							}

							if ($answer->getAttribute('done') != 0) {
								$questionsCount++;
								$totalTime += $answer->getAttribute('time');
							}

							$teacherRating = null;
							$systemRating = null;
							$studentRatings = [];
							foreach($answer->answerRatings as $answerRating) {
								if ($answerRating->getAttribute('type') === 'STUDENT' && $answerRating->getAttribute('rating') != null && !in_array($answerRating->getAttribute('user_id'), $userHasRated)) {
									$studentRatings[] = $answerRating->getAttribute('rating');
								} elseif($answerRating->getAttribute('type') === 'SYSTEM') {
									$systemRating = $answerRating->getAttribute('rating');
								} elseif($answerRating->getAttribute('type') === 'TEACHER') {
									$teacherRating = $answerRating->getAttribute('rating');
								}
							}

							$studentRatings = array_unique($studentRatings);
							if ($teacherRating !== null ||  $systemRating !== null) {
								$wantedRating = ($teacherRating !== null) ? $teacherRating : $systemRating;
								foreach($answer->answerRatings as $answerRating) {

									if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds) && in_array($answerRating->getAttribute('user_id'), $userHasRated)) {
										$testParticipantId = $testParticipantUserIds[$answerRating->getAttribute('user_id')];
										if ($answerRating->getAttribute('type') === 'STUDENT' && $answerRating->getAttribute('rating') != $wantedRating && array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
											if (!array_key_exists($testParticipantId, $testParticipantAbnormalities)) {
												$testParticipantAbnormalities[$testParticipantId] = 0;
											}
											$testParticipantAbnormalities[$testParticipantId]++;
										}
									}
								}
							} elseif (count($studentRatings) > 1) {
								foreach($answer->answerRatings as $answerRating) {
									if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds) && in_array($answerRating->getAttribute('user_id'), $userHasRated)) {
										$testParticipantId = $testParticipantUserIds[$answerRating->getAttribute('user_id')];
										if (array_key_exists($answerRating->getAttribute('user_id'), $testParticipantUserIds)) {
											if (!array_key_exists($testParticipantId, $testParticipantAbnormalities)) {
												$testParticipantAbnormalities[$testParticipantId] = 0;
											}
											$testParticipantAbnormalities[$testParticipantId]++;
										}
									}
								}
							}
						}

						$testParticipant->setAttribute('score', $score);
						$testParticipant->setAttribute('max_score', $maxScore);
						$testParticipant->setAttribute('questions', $questionsCount);
						$testParticipant->setAttribute('total_time', $totalTime);
						$testParticipant->setAttribute('answer_require_rating', $answerRequireRating);
						$relations = $testParticipant->getRelations();
						unset($relations['answers']);
						//$relations['answer_longest_time'];
						$testParticipant->setRelations($relations);
					}

					foreach($testParticipants as $testParticipant) {
						if (array_key_exists($testParticipant->getKey(), $testParticipantAbnormalities)) {
							$testParticipant->setAttribute('abnormalities', $testParticipantAbnormalities[$testParticipant->getKey()]);
						} else {
							$testParticipant->setAttribute('abnormalities', 0);
						}
					}
				}
				return Response::make($testParticipants, 200);
				break;
			case 'list':
				return Response::make($testParticipants->get()->keyBy('id'), 200);
				break;
			case 'paginate':
			default:
				return Response::make($testParticipants->paginate(15), 200);
				break;
		}
	}

	/**
	 * Store a newly created test participant in storage.
	 *
	 * @param CreateTestParticipantRequest $request
	 * @return Response
	 */
	public function store(TestTake $testTake, CreateTestParticipantRequest $request)
	{
		$testTakeParticipantFactory = new Factory(new TestParticipant());
		$testParticipants = $testTakeParticipantFactory->generateMany($testTake->getKey(), $request->all());

		if ($testTake->testParticipants()->saveMany($testParticipants) !== false) {
			return Response::make($testParticipants, 200);
		} else {
			return Response::make('Failed to create test participant(s)', 500);
		}
	}

	/**
	 * Display the specified test participant.
	 *
	 * @param  TestParticipant  $testParticipant
	 * @return Response
	 */
	public function show(TestTake $testTake, TestParticipant $testParticipant, Request $request)
	{
		if ($testParticipant->test_take_id !== $testTake->getKey()) {
			return Response::make('Test participant not found', 404);
		} else {
			$test = $testTake->test;
			if (is_array($request->get('with')) && in_array('participantStatus', $request->get('with'))) {
				$testParticipant->load([
					'user',
					'testTakeStatus',
					'schoolClass',
					'answers',
					'testTakeEvents',
					'user.averageRatings' => function ($query) use ($testParticipant, $test) {
						$query->where('school_class_id', $testParticipant->getAttribute('school_class_id'))->where('subject_id', $test->getAttribute('subject_id'));
					}
				]);

				$isInvigilator = false;
				$roles = $this->getUserRoles();

				if (in_array('Teacher', $roles) || in_array('Invigilator', $roles)) {
					foreach ($testTake->invigilatorUsers as $user) {
						if ($user->getKey() == Auth::id()) {
							$isInvigilator = true;
							break;
						}
					}
				}

				if ($isInvigilator) {
					$questions = QuestionGatherer::getQuestionsOfTest($testTake->getAttribute('test_id'), true);

					foreach ($questions as $questionId => $question) {
						$questions[$questionId] = $question['score'];
					}

					$score = 0;
					$madeScore = 0;
					$maxScore = 0;
					$questionsCount = 0;
					$totalTime = 0;
					$longestAnswer = null;

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

							if ($answerScore !== null && $answer->getAttribute('ignore_for_rating') == 0) {
								$score += $answerScore;
							}

							if ($answer->getAttribute('ignore_for_rating') == 0) {
								$maxScore += $questions[$answerQuestionId];
							}
						}

						if ($answer->getAttribute('done') != 0) {
							$questionsCount++;
							$totalTime += $answer->getAttribute('time');
							if (array_key_exists($answerQuestionId, $questions)) {
								$madeScore += $questions[$answerQuestionId];
							}
						}

						if ($answer->getAttribute('time') > 0 && ($longestAnswer === null || $longestAnswer->getAttribute('time') > $answer->getAttribute('time'))) {
							$longestAnswer = $answer;
						}
					}

					if ($longestAnswer !== null) {
						$longestAnswer->load('question');
						if ($longestAnswer->question instanceof QuestionInterface) {
							$longestAnswer->question->loadRelated();
						}
					}

					$testParticipant->setAttribute('score', $score);
					$testParticipant->setAttribute('made_score', $madeScore);
					$testParticipant->setAttribute('max_score', $maxScore);
					$testParticipant->setAttribute('questions', $questionsCount);
					$testParticipant->setAttribute('total_time', $totalTime);

					$relations = $testParticipant->getRelations();
					$relations['longest_answer'] = $longestAnswer;
					$testParticipant->setRelations($relations);
				}
			} else {
				$testParticipant->load('user', 'testTakeStatus', 'schoolClass', 'answers', 'testTakeEvents');
			}

			return Response::make($testParticipant, 200);
		}
	}

	/**
	 * Update the specified test participant in storage.
	 *
	 * @param  TestParticipant $testParticipant
	 * @param UpdateTestParticipantRequest $request
	 * @return Response
	 */
	public function update(TestTake $testTake, TestParticipant $testParticipant, UpdateTestParticipantRequest $request)
	{
		$testParticipant->fill($request->all());

		if ($testTake->testParticipants()->save($testParticipant) !== false) {
			return Response::make($testParticipant, 200);
		} else {
			return Response::make('Failed to update test participant', 500);
		}
	}

	/**
	 * Remove the specified test participant from storage.
	 *
	 * @param  TestParticipant  $testParticipant
	 * @return Response
	 */
	public function destroy(TestTake $testTake, TestParticipant $testParticipant)
	{
		if ($testParticipant->test_take_id !== $testTake->getKey()) {
			return Response::make('Test participant not found', 404);
		}

		if ($testParticipant->delete()) {
			return Response::make($testParticipant, 200);
		} else {
			return Response::make('Failed to delete test participant', 500);
		}
	}

	public function heartbeat(TestTake $testTake, TestParticipant $testParticipant, HeartbeatTestParticipantRequest $request) {
		if ($testParticipant->test_take_id !== $testTake->getKey()) {
			return Response::make('Test participant not found', 404);
		}

		$testParticipant->load('testTake', 'testTake.discussingParentQuestions', 'testTake.testTakeStatus', 'testTakeStatus', 'testTakeEvents', 'testTakeEvents.testTakeEventType');

		$testParticipant->setAttribute('heartbeat_at', Carbon::now());
		$testParticipant->setAttribute('ip_address', $request->get('ip_address'));

		if ($request->has('answer_id')) {
			$testParticipant->setAttribute('answer_id', $request->get('answer_id'));
		}

		if ($testParticipant->save() !== false) {
			$alert = false;

			foreach($testParticipant->testTakeEvents as $testTakeEvent) {
				if ($testTakeEvent->testTakeEventType->requires_confirming == 1 && $testTakeEvent->confirmed == 0) {
					$alert = true;
					break;
				}
			}

			$testParticipant->setAttribute('alert', $alert);
			return Response::make($testParticipant, 200);
		} else {
			return Response::make('Failed to process heartbeat of test participant', 500);
		}
	}

}
