<?php

namespace tcCore\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Test;

class ExamTestsDebug extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'examschool:debug';

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
            $output = new ConsoleOutput();
            $examSchoolLocation = \tcCore\SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first();
            if(is_null($examSchoolLocation)){
                throw new Exception('examschool not found');
            }
            $tests = Test::where('owner_id',$examSchoolLocation->getKey())->where('scope','!=','exam')->get();
            $output->writeln('<info>testscount:'.$tests->count().'</info>') ;
            foreach ($tests as $test){
                $output->writeln('<info>test id:'.$test->getKey().'</info>') ;
                $output->writeln('<info>hasNonPublishableExamSubject:'.$test->hasNonPublishableExamSubject().'</info>') ;
                if($test->hasNonPublishableExamSubject()){
                    continue;
                }
//                $test->setExamTestParams();
//                $test->save();
//                $test->setExamParamsOnQuestionsOfTest();
            }
        }catch (Exception $e){
            $output = new ConsoleOutput();
            $output->writeln('<error>'.$e->getMessage().'</error>') ;
        }
    }
}
