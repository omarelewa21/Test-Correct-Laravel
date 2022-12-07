<?php

namespace Tests\Unit\Http\Controllers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use tcCore\Console\Commands\duplicateTestTake;
use tcCore\FileManagement;
use tcCore\Http\Controllers\FileManagementController;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestQuestion;
use Tests\TestCase;

class FileManagementControllerTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testDuplicateTestToSchool()
    {
        //login as account manager
        Auth::loginUsingId(520);

        $test = Test::find(238);

        $testCount = Test::count();
        $testQuestionsCount = TestQuestion::count();
        $questionsCount = Question::count();

        $original_school_location = $test->owner_id;
        $original_author = $test->author_id;
        $original_period = $test->period_id;
        $original_subject = $test->subject_id;

        $fileManagement = FileManagement::find('8b956983-8c68-4450-b4a5-abee30988572');


        (new FileManagementController())->duplicateTestToSchool(new Request(), $fileManagement);


        $createdTest = Test::orderByDesc('created_at')->first();


        $this->assertNotEquals($original_author, $createdTest->author_id);
        $this->assertNotEquals($original_school_location, $createdTest->owner_id);
        $this->assertNotEquals($original_period, $createdTest->period_id);
        $this->assertNotEquals($original_subject, $createdTest->subject_id);

        $this->assertGreaterThan($testCount, Test::count());
        $this->assertGreaterThan($testQuestionsCount, TestQuestion::count());
        $this->assertGreaterThan($questionsCount, Question::count());
    }


    /** @test */
    public function example()
    {
        $numberOfQuestionsIncSub = 7;

        $allQuestions = Test::find(3)->listOfTakeableTestQuestions()->map->question;

        $original_status = $allQuestions->reduce(function ($carry, $question) {
            $carry->add([$question->getKey(),$question->parentInstance->add_to_database_disabled]);

            if($question->subQuestions){
                $carry->add($question->subQuestions->map(function ($subQuestion) {
                    return [$subQuestion->getKey(), $subQuestion->parentInstance->add_to_database_disabled];
                }));
            }

            return $carry;
        }, collect())->flatten();



        $questionIds = $allQuestions->reduce(function ($carry, $question) {
            $carry->add($question->getKey());

            if($question->subQuestions){
                $carry->add($question->subQuestions->map(function ($subQuestion) {
                    return $subQuestion->getKey();
                }));
            }

            return $carry;
        }, collect())->flatten()->filter()->values();


        $changed = DB::table('questions')->whereIn('id', $questionIds)->update(['add_to_database_disabled' => 1]);


        dd($original_status, $questionIds, $changed);
        dd($questionIds);

        dd(Test::find(3)->listOfTakeableTestQuestions()->map->question);
    }
}
