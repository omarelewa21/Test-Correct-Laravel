<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\Question;
use tcCore\QuestionAuthor;

class FixIsSubquestionInGroupQuestionMembers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:subquestionGroup {questionId} {groupQuestionQuestionId}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'duplicates question that is group member and has is_subquestion false';

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
        $questionId = $this->argument('questionId');
        if(!$this->questionIsNotSubquestion($questionId)){
            $this->info(sprintf('questionId:%d is already sub_question! No further action',$questionId));
            return;
        }
        $this->loginAuthor($questionId);
        $groupQuestionQuestionId = $this->argument('groupQuestionQuestionId');
        try{
            $groupQuestionQuestion = GroupQuestionQuestion::withTrashed()->where('question_id',$questionId)->where('group_question_id',$groupQuestionQuestionId)->firstOrFail();
            $subQuestion = $groupQuestionQuestion->question()->withTrashed()->first();
            $subQuestionCopy = $subQuestion;
            if(!$subQuestion->trashed()){
                $subQuestionCopy = $subQuestion->duplicate([]);
            }
            $subQuestionCopy->getQuestionInstance()->setAttribute('is_subquestion', 1);
            $groupQuestionQuestion->setAttribute('question_id', $subQuestionCopy->getKey());
            $subQuestionCopy->getQuestionInstance()->save();
            $groupQuestionQuestion->save();
        }catch (\Exception $e){
            $this->info($e->getMessage());
        }
        Auth::logout();
        return 0;
    }

    private function questionIsNotSubquestion($questionId)
    {
        $question = Question::withTrashed()->findOrFail($questionId);
        if($question->is_subquestion){
            return false;
        }
        return true;
    }

    private function loginAuthor($questionId)
    {
        $author = QuestionAuthor::withTrashed()->where('question_id',$questionId)->orderBy('created_at','desc')->firstOrFail();
        $user = $author->user()->withTrashed()->first();
        Auth::login($user);
    }
}
