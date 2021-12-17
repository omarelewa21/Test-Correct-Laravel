<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\TestTake;

class StartAndStopAssessments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assessment:start_and_stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Changes test_takes_status_id of test_takes of type assessment based on time start and time end';

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
        $this->handleStart();

        $this->handleStop();

        return 0;
    }

    private function handleStart()
    {
        TestTake::typeAssessment()->statusPlanned()->shouldStart()->select('test_takes.*')->get()->each(function (TestTake $tt) {
            $tt->updateToTakingTest();
        });
    }

    private function handleStop()
    {
        TestTake::typeAssessment()->statusTakingTest()->shouldEnd()->select('test_takes.*')->get()->each(function(TestTake $tt) {
            $tt->updateToTaken();
        });
    }
}
