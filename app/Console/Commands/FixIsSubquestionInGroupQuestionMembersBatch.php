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
        $results = DB::select(DB::raw($this->getSQL()));
        foreach($results as $resultObject){
            $this->call('fix:subquestionGroup', [
                'questionId' => $resultObject->question_id,
                'groupQuestionQuestionId' => $resultObject->group_question_id
            ]);
        }
    }

    public function getSQL()
    {
        return 'SELECT questions.id          AS question_id,
                       t2.id 				 AS group_question_id
                FROM   tests
                       INNER JOIN test_questions
                               ON tests.id = test_questions.test_id
                       INNER JOIN group_question_questions
                               ON test_questions.question_id =
                                  group_question_questions.`group_question_id`
                       LEFT JOIN questions
                              ON group_question_questions.question_id = questions.id
                       LEFT JOIN group_questions AS t2
                              ON group_question_questions.group_question_id = t2.id
                WHERE  group_question_questions.question_id IN (
                    SELECT
                       test_questions.question_id
                    FROM   `test_questions`
                              INNER JOIN group_question_questions
                                      ON test_questions.question_id =
                                         group_question_questions.question_id
                              LEFT JOIN questions
                                     ON test_questions.question_id = questions.id
                    WHERE  is_subquestion = 0
                )
                GROUP  BY question_id,
                          group_question_id
                ORDER  BY question_id';
    }
}
