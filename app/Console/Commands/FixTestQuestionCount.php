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
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;

class FixTestQuestionCount extends Command
{

    use BaseCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:tqc';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix the number of questions for a test (which is saved as a column value)';

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

        $daysBack = $this->ask('How many days do you want to go back to fix the tests', 30);

        $tests = Test::where('updated_at', '>=', Carbon::now()->subDays($daysBack)->toDateTimeString())->get();
        $counts= [
            'fixed' => 0,
            'total' => $tests->count(),
            ];
        $tests->each(function (Test $t) use (&$counts, $dryRun) {
            $needsSave = false;
            $old = $t->question_count;
            $new = $t->getQuestionCount();
            if ($old != $new) {
                $counts['fixed']++;
                $needsSave = true;
                $this->error(sprintf('Test with id %d and title `%s` => from %d to %d', $t->getKey(), $t->name, $old, $new));
            } else {
                $this->info(sprintf('Test with id %d and title `%s` => from %d to %d', $t->getKey(), $t->name, $old, $new));
            }

            if (!$dryRun && $needsSave) {
                $t->question_count = $new;
                $t->save();
            }
        });


        $duration = microtime(true) - $start;
        $this->info(sprintf('done, checked %d records and fixed %d in %f seconds', $counts['total'], $counts['fixed'], $duration));
    }
}
