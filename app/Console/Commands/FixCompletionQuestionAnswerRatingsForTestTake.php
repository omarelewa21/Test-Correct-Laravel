<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\CompletionQuestion;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Http\Helpers\CompletionQuestionAnswerHelper;
use tcCore\Http\Helpers\TestTakeRecalculationHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;

class FixCompletionQuestionAnswerRatingsForTestTake extends Command
{

    use BaseCommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:testTake';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix completion question answer ratings (wrong right answer)';

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

        $testTakeId = $this->ask('What is the id of the test take you want to fix?');

        $dryRun = !$this->confirm('Do you want to SKIP the dry run?');

        if(Uuid::isValid($testTakeId)){
            $testTake = TestTake::whereUuid($testTakeId)->first();
        }
        else {
            $testTake = TestTake::Find($testTakeId);
        }
        if(!$testTake){
            $this->error('could not find a corresponding test take');
            exit;
        } else if($testTake->test_take_status_id < 6){
            $this->error('This test has not reached the status taken yet');
            exit;
        }

        $user = $testTake->user;
        $name = sprintf('%s %s %s',$user->name_first, $user->name_suffix, $user->name);
        $this->warn(sprintf('%s%s%s%s%s%s%s%s<info>Status: %s</info>',$user->username,PHP_EOL,$name, PHP_EOL, $testTake->test->name, PHP_EOL, $testTake->time_start,PHP_EOL,$testTake->testTakeStatus->name));

        if(!$this->confirm('Is dit de test take you expected')){
            $this->error('exited as you did not want to run this on this test take');
            exit;
        }

        $testTakeRecalculationHelper = new TestTakeRecalculationHelper($this);
        $report = (object) [
            'ratingChanged' => false,
            'teacherRated' => false,
            'endRated' => false
        ];

        if($testTakeRecalculationHelper->recalculateSystemRatingsForTestTake($testTake, $dryRun)){
            $report->ratingChanged = true;
            // we had changes, did we have a teacher who did the rating?
            if($testTakeRecalculationHelper->hasTeacherRatingsForTestTake($testTake)){
                $report->teacherRated = true;
            }

            if($testTake->test_take_status_id == 9) {
                $report->endRated = true;
            }
        }

        $duration = microtime(true) - $start;

        $preText = 'Updated';
        if($dryRun){
            $preText = 'Would have updated';
        }
        $this->info('');
        $this->info('*****************');
        $this->info(sprintf('%s <warning>%d</warning> answsers',$preText, $testTakeRecalculationHelper->getUpdatedAnswerCount()));
        $this->info('*****************');
        $this->info('');


        if($report->endRated === true) {
            $this->warn('AANGEPAST EN EINDRATING GEHAD');
        } else if($report->teacherRated === true) {
            $this->warn('AANGEPAST EN DOOR DOCENT NAGEKEKEN');
        } else if($report->ratingChanged === true) {
            $this->warn('AANGEPAST');
        }
        $this->info('');
    }
}
