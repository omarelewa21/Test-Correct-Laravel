<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
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

class FixCompletionQuestionAnswerRatings extends Command
{

    use BaseCommandTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:cqr';

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

        $dryRun = !$this->confirm('Do you want to SKIP the dry run?');

        $completionQuestionsIdsBuilder = CompletionQuestion::whereIn('id',
            CompletionQuestionAnswerLink::where('updated_at','>=', '2020-10-22 12:00:00')
                ->where('updated_at','<=','2020-10-22 21:00:00')
                ->select('completion_question_id')
        )->select('id');

        $testIdsBuilder = TestQuestion::whereIn('question_id',$completionQuestionsIdsBuilder)->groupBy('test_id')->select('test_id');

        $testTakes = TestTake::whereIn('test_take_status_id',[7,8,9])->whereIn('test_id',$testIdsBuilder)->get();

        $testTakeRecalculationHelper = new TestTakeRecalculationHelper($this);
        $report = (object) [
            'ratingChanged' => collect([]),
            'teacherRated' => collect([]),
            'endRated' => collect([])
        ];
        $testTakes->each(function(TestTake $tt) use ($testTakeRecalculationHelper, $report, $dryRun) {
            if($testTakeRecalculationHelper->recalculateSystemRatingsForTestTake($tt, $dryRun)){
                $report->ratingChanged->push($tt);
                // we had changes, did we have a teacher who did the rating?
                if($testTakeRecalculationHelper->hasTeacherRatingsForTestTake($tt)){
                    $report->teacherRated->push($tt);
                }

                if($tt->test_take_status_id == 9) {
                    $report->endRated->push($tt);
                }
            }
        });


        $duration = microtime(true) - $start;
        $this->error('AANGEPAST');
        $report->ratingChanged->each(function(TestTake $tt){
            $user = $tt->user;
            $name = sprintf('%s %s %s',$user->name_first, $user->name_suffix, $user->name);
            $this->info(sprintf('%s#%s#%s#%s',$user->username,$name, $tt->test->name, $tt->time_start));
        });
        $this->info(sprintf('TOTAAL AANGEPAST: %d',$report->ratingChanged->count()));

        $this->info('');
        $this->error('AANGEPAST EN DOOR DOCENT NAGEKEKEN');
        $report->teacherRated->each(function(TestTake $tt){
            $user = $tt->user;
            $name = sprintf('%s %s %s',$user->name_first, $user->name_suffix, $user->name);
            $this->info(sprintf('%s#%s#%s#%s',$user->username,$name, $tt->test->name, $tt->time_start));
        });
        $this->info(sprintf('TOTAAL ANGEPAST EN DOOR DOCENT NAGEKEKEN: %d',$report->teacherRated->count()));

        $this->info('');
        $this->error('AANGEPAST EN EINDRATING GEHAD');
        $report->endRated->each(function(TestTake $tt){
            $user = $tt->user;
            $name = sprintf('%s %s %s',$user->name_first, $user->name_suffix, $user->name);
            $this->info(sprintf('%s#%s#%s#%s',$user->username,$name, $tt->test->name, $tt->time_start));
        });
        $this->info(sprintf('TOTAAL AANGEPAST EN EINDRATING GEHAD: %d',$report->endRated->count()));

    }
}
