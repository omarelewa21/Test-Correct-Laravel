<?php

namespace tcCore\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use tcCore\Test;

class ExamTestsQuery extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examschool:update_tests';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        try {
            $examSchoolLocation = \tcCore\SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->firstOrFail();
            if(is_null($examSchoolLocation)){
                throw new Exception('examschool not found');
            }
            $tests = Test::where('owner_id',$examSchoolLocation->getKey())->where('abbreviation','CE')->get();
            foreach ($tests as $test){
                if($test->hasNonPublishableExamSubjectDemo()){
                    continue;
                }
                $test->setExamTestParams();
                $test->save();
                $test->setExamParamsOnQuestionsOfTest();
            }
        }catch (Exception $e){
            $this->error($e->getMessage());
        }
    }
}
