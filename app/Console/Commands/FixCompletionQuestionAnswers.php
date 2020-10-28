<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Http\Helpers\CompletionQuestionAnswerHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\TestParticipant;
use tcCore\TestTake;

class FixCompletionQuestionAnswers extends Command
{

    use BaseCommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:cqa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix completion question answers (wrong right answer)';

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
        ini_set('memory_limit', '-1');

        $start = microtime(true);

        $dryRun = !$this->confirm('Do you want to skip the dry run?');

        $counts = (new CompletionQuestionAnswerHelper($this, $dryRun))->fixQuestions();


        $duration = microtime(true) - $start;
        $this->info(sprintf('done, checked %d completion question records and fixed %d in %f seconds',$counts['total'],$counts['fixed'], $duration));
    }
}
