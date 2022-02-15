<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;
use tcCore\Http\Controllers\AuthorsController;
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
            $examSchoolLocation = \tcCore\SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first();
            if(is_null($examSchoolLocation)){
                throw new Exception('examschool not found');
            }
            $examSchoolUser = AuthorsController::getCentraalExamenAuthor();
            if(is_null($examSchoolUser)){
                throw new Exception('examschool user not found');
            }
            Auth::login($examSchoolUser);
            $tests = Test::where('owner_id',$examSchoolLocation->getKey())->where(function ($q) {
                $q->where('scope','!=','exam')->orWhereNull('scope');
            })->get();
            foreach ($tests as $test){
                if($test->hasNonPublishableExamSubject()){
                    continue;
                }
                $test->setExamTestParams();
                $test->abbreviation = 'EXAM';
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
