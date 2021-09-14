<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\QuestionAuthor;
use tcCore\Test;
use tcCore\TestQuestion;

class FixIsSubquestionTrueForNonGroupQuestionMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:isSubquestionTrueNonGroupMember';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'duplicates tests with faulty question(s) by modifying the questoin(s)';


    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $results = \DB::select(\DB::raw($this->getSQL()));
        try {
            foreach ($results as $resultObject) {
                $this->updateOrCopyTestQuestion($resultObject->question_id, $resultObject->test_id, $resultObject->test_question_id);
                $this->setSystemTestIdToNull($resultObject->test_id);
            }
        }catch(\Exception $e){
            $this->info($e->getMessage());
        }
    }

    private function updateOrCopyTestQuestion($questionId,$testId,$testQuestionId)
    {
        $this->loginAuthor($questionId);
        $test = Test::find($testId);
        $testQuestion = TestQuestion::find($testQuestionId);
        if(is_null($test)||is_null($testQuestion)){
            Auth::logout();
            return;
        }
        $request  = new Request();
        $params = [
            'session_hash' => Auth::user()->session_hash,
            'user'         => Auth::user()->username,
            'id' => $testQuestionId,
//            'subject_id' => $test->subject_id,
//            'education_level_id' => $test->education_level_id,
//            'education_level_year' => $test->education_level_year,
//            'education_level_year' => $test->education_level_year,
            'is_subquestion' => 0,
        ];
        $testQuestionQuestionId = $questionId;
        $request->merge($params);
        $response = (new TestQuestionsController())->updateFromWithin($testQuestion,  $request);

        Auth::logout();
    }

    private function setSystemTestIdToNull($testId)
    {
        $test = Test::withTrashed()->find($testId);
        if(!is_null($test->system_test_id)){
            $test->system_test_id = null;
            $test->save();
        }
    }

    private function getSQL()
    {
        return 'select tests.id as test_id,test_questions.question_id as question_id, test_questions.id as test_question_id 
                    from tests
                        left join test_questions
                            on tests.id = test_questions.test_id
                        left join `questions`	
                            on test_questions.question_id = questions.`id`
                    where is_subquestion = 1 and questions.deleted_at is null and tests.deleted_at is null and test_questions.deleted_at is null and is_system_test = 0
                    order by tests.id
                    ';
    }

    private function loginAuthor($questionId)
    {
        $author = QuestionAuthor::withTrashed()->where('question_id',$questionId)->orderBy('created_at','desc')->firstOrFail();
        Auth::login($author->user()->withTrashed()->first());
    }
}
