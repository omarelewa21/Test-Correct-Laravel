<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Answer;
use tcCore\AnswerParentQuestion;
use tcCore\Http\Helpers\AnswerParentQuestionsHelper;
use tcCore\Lib\Question\QuestionGatherer;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;

class FixAnswerParentQuestionsAll extends Command
{

    use BaseCommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:allApq';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'fix missing answer parent questions for all';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        set_time_limit(300);
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $start = microtime(true);

        $locations = 0;
        $obj = (object) [
            'locations' => 0,
            'users' => 0,
            'answers' => 0,
            'tests' => 0
        ];

        $helper = new AnswerParentQuestionsHelper($this);
        SchoolLocation::all()->each(function($schoolLocation) use ($obj, $helper) {
            $this->comment(sprintf('New School %s', $schoolLocation->name));
            $obj->locations++;
            $data = (array) $helper->fixAnswerParentQuestionsPerSchoolLocation($schoolLocation);
            foreach($data as $key => $val){
                $obj->$key += $val;
            }
            $schoolLocation = null;
        });
        $duration = microtime(true) - $start;
        $this->info(sprintf('done, found %d teachers, checked %d test takes and added %d AnswerParentQuestion records in %f seconds ',$obj->users,$obj->tests, $obj->answers, $duration));
        $this->comment(' ');
    }
}
