<?php namespace tcCore\Http\Controllers\TestParticipants;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Answer;
use tcCore\Http\Requests\CreateAnswerRequest;
use tcCore\Http\Requests\UpdateAnswerRequest;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestTake;

class Answers2019Controller extends Controller {

    public function getAnswersStatusAndQuestions(TestParticipant $testParticipant, Request $request){
        $answers = Answer::where('test_participant_id',$testParticipant->getKey())->orderBy('order')->get();
        $questions = collect([]);
        $qh = new QuestionHelper();
        $answers->each(function($answer) use ($questions,$qh){
            $questions->add($qh->getTotalQuestion($answer->question));
        });
        return Response::make([
            'answers' => $answers,
            'questions' => $questions,
            'participant_test_take_status_id' => $testParticipant->test_take_status_id,
        ],
            200);

    }

    /**
     * WITHOUT test take
     * @param TestParticipant $testParticipant
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAnswersAndStatus(TestParticipant $testParticipant, Request $request){
        $answers = Answer::where('test_participant_id',$testParticipant->getKey())->orderBy('order')->get();
        return Response::make([
            'answers' => $answers,
            'participant_test_take_status_id' => $testParticipant->test_take_status_id,
        ],
            200);

    }

     /**
     * WITH test take
     * @param TestParticipant $testParticipant
     * @param TestTake $testTake
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getAnswersStatusAndTestTake(TestParticipant $testParticipant, TestTake $testTake, Request $request){
        $answers = Answer::where('test_participant_id',$testParticipant->getKey())->orderBy('order')->get();
        return Response::make([
            'answers' => $answers,
            'test_take' => $testTake,
            'participant_test_take_status_id' => $testParticipant->test_take_status_id,
        ],
            200);

    }

    public function showQuestionAndAnswer(TestParticipant $testParticipant, Question $question, Request $request){
        $answer = Answer::where('test_participant_id',$testParticipant->getKey())
                            ->where('question_id',$question->getKey())
                            ->with('answerParentQuestions','Question', 'answerParentQuestions', 'answerParentQuestions.groupQuestion', 'answerParentQuestions.groupQuestion.attachments')
                            ->first();

//        if ($answer && $answer->question instanceof QuestionInterface) {
//            $answer->question->loadRelated();
//        }

        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
        }, 'pValue.educationLevel']);

        if($question instanceof QuestionInterface) {
            $question->loadRelated();
        }

        if($answer !== null){
            // added as replacement of hearbeat input 20190830
            $testParticipant->setAttribute('answer_id',$answer->getKey());
            $testParticipant->setAttribute('heartbeat_at',Carbon::now());
            if($request->has('ip_address')){
                $testParticipant->setAttribute('ip_address', $request->get('ip_address'));
            }
            $testParticipant->save();
        }
        return Response::make([
            'answer' => $answer,
            'question' => $question
            ],
            200);
    }

    protected function hasNextQuestion(TestParticipant $testParticipant, $currentIndex)
    {
        return $currentIndex+1 < Answer::where('test_participant_id',$testParticipant->getKey())->count();

    }

	/**
	 * Update the specified answer in storage.
	 *
	 * @param  Answer $answer
	 * @param UpdateAnswerRequest $request
	 * @return Response
	 */
	public function update(TestParticipant $testParticipant, Answer $answer, UpdateAnswerRequest $request)
	{
//        $question = $this->AnswersService->getParticipantQuestion($question_id);
//        $questions = $this->TestTakesService->getParticipantQuestions($participant_id);

//        $data['mode'] = 'all';
//        $data['with'] = ['question'];
//
//        $response = $this->Connector->getRequest('/test_participant/' . $participant_id . '/answer', $data);


//        if(isset($questions[$take_question_index + 1])) {
//            echo json_encode([
//                'status' => 'next',
//                'take_id' => $take_id,
//                'question_id' => ($take_question_index + 1)
//            ]);
//        }else{
//            echo json_encode([
//                'status' => 'done'
//            ]);
//        }

        $question = Question::find($request->input('question_id'));
        $question->getQuestionInstance()->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
            $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
        }, 'pValue.educationLevel']);

        if($question instanceof QuestionInterface) {
            $question->loadRelated();
        }

		$answer->fill($request->all());

		if ($testParticipant->answers()->save($answer) !== false) {

            $response = $answer;
		    if($request->has('take_id') && $request->has('take_question_index') && $request->has('take_id')) {
		        $nextTakeQuestionIndexNr = $request->input('take_question_index')+1;
                if($this->hasNextQuestion($testParticipant, $request->input('take_question_index'))) {
                    $response = json_encode([
                        'success' => true,
                        'status' => 'next',
                        'take_id' => $request->input('take_id'),
                        'question_id' => $nextTakeQuestionIndexNr
                    ]);
                }else{
                    $response = json_encode([
                        'success' => true,
                        'status' => 'done'
                    ]);
                }
            }
			return Response::make($response, 200);
		} else {
			return Response::make('Failed to update answer', 500);
		}
	}

}
