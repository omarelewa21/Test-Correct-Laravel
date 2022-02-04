<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $examSchoolLocation = \tcCore\SchoolLocation::where('customer_code', 'OPENSOURCE1')->firstOrFail();
            if(is_null($examSchoolLocation)){
                throw new Exception('examschool not found');
            }
            $tests = Test::where('owner_id',$examSchoolLocation->getKey())->whereNot('scope','exam')->get();
            foreach ($tests as $test){
                if($test->hasNonPublishableExamSubject()){
                    continue;
                }
                $test->setExamTestParams();
                $test->save();
                $test->setExamParamsOnQuestionsOfTest();
            }
        }catch (Exception $e){
            $this->command->error($e->getMessage());
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
