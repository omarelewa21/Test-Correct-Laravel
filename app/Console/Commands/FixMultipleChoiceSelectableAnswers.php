<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use tcCore\Log;
use tcCore\MultipleChoiceQuestion;
use tcCore\User;

class FixMultipleChoiceSelectableAnswers extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:multiplechoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'restore deleted user';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $list = collect(DB::select( "SELECT
                    *
                    FROM
                    multiple_choice_questions MCQ
                    INNER JOIN
                    (SELECT
                    MCQAL.multiple_choice_question_id, COUNT(*) AS amount_right
                    FROM
                    multiple_choice_question_answer_links MCQAL
                    INNER JOIN multiple_choice_question_answers MCA ON MCQAL.multiple_choice_question_answer_id = MCA.id
                    WHERE
                    MCA.score > 0 AND MCA.deleted_at IS NULL
                    AND MCQAL.deleted_at IS NULL
                    GROUP BY MCQAL.multiple_choice_question_id) MQAL ON MQAL.multiple_choice_question_id = MCQ.id
                    AND MQAL.amount_right > 1
                    WHERE
                    selectable_answers <= 1
                      AND subtype = 'MultipleChoice'
                    AND deleted_at IS NULL")
        );
        if(app()->runningInConsole()) {
            $bar = $this->output->createProgressBar(count($list));
            $bar->start();
        }
        $list->each(function($m) use ($bar){
            $q = MultipleChoiceQuestion::find($m->id);
            if($q){
                $q->selectable_answers = $m->amount_right;
                $q->save();
            }
            if(app()->runningInConsole()) {
                $bar->advance();
            }
        });
        if(app()->runningInConsole()) {
            $bar->finish();
        }
    }
}
