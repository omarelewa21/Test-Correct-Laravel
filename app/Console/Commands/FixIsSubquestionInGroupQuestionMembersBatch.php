<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixIsSubquestionInGroupQuestionMembersBatch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:subquestionGroupBatch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'duplicates question that is group member and has is_subquestion false from sql batch';

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
        $results = DB::select($this->getSQL());
        foreach($results as $resultObject){
            $this->call('fix:subquestionGroup', [
                'questionId' => $resultObject->question_id,
                'groupQuestionQuestionId' => $resultObject->group_question_id
            ]);
        }
    }

    public function getSQL(): string
    {
        return 'select distinct t.id as question_id, group_question_questions.group_question_id from
                ((select * from questions where is_subquestion = 0) as t 
            inner join group_question_questions on (t.id = group_question_questions.question_id)
        )';
    }
}
