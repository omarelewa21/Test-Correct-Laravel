<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Test;

class UpdateExamTestsAndQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
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
                $test->setExamTestParams();
                $test->save();
                $test->setExamParamsOnQuestionsOfTest();
            }
        }catch (Exception $e){
            $output = new ConsoleOutput();
            $output->writeln('<error>'.$e->getMessage().'</error>') ;
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
