<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class NationalItemBankTest extends TestCase
{

    use DatabaseTransactions;

    //todo test:
    // werkt unpublishing naar behoren
    // komen alle drie de scopes voor in de dataset

    /** @test */
    public function can_retrieve_valid_nationalItemBank_testsFiltered_with_only_the_three_valid_scopes()
    {
        \Auth::login(self::getTeacherOne()); //Auth::User needs access to new testOverview (school_location -> allow_new_test_bank)
        $validScopes = ['ldt', 'exam', 'cito'];
        $testsFiltered = Test::nationalItemBankFiltered()->get();

        if($testsFiltered->isEmpty()){
            $this->fail('resultset is empty, no valid tcCore\Test records where available in the database');
        }
        $testsFiltered->each(function ($test) use ($validScopes) {
            $this->assertTrue(in_array($test->scope, $validScopes));
        });
    }

    /** @test */
    public function nationalItemBank_filteredTests_contain_tests_with_all_three_test_scopes_ldt_exam_cito()
    {
        \Auth::login(self::getTeacherOne()); //Auth::User needs access to new testOverview (school_location -> allow_new_test_bank)
        $scopeInDataset = collect([
            'ldt' => false,
            'exam' => false,
            'cito' => false,
        ]);
        $testsFiltered = $this->getNationalItemBankDatasource();

        if($testsFiltered->isEmpty()){
            $this->fail('dataset is empty, no valid tcCore\Test records where available in the database');
        }
        $testsFiltered->each(function ($test) use (&$scopeInDataset) {
            $scopeInDataset[$test->scope] = true;
        });
        $scopeInDataset->each(function ($scopeIsPresentInDataset) {
            $this->assertTrue($scopeIsPresentInDataset);
        });
    }

    /** @test */
    public function can_retrieve_a_valid_nationalItemBank_dataset_with_only_the_three_valid_scopes()
    {
        \Auth::login(self::getTeacherOne()); //Auth::User needs access to new testOverview (school_location -> allow_new_test_bank)
        $validScopes = ['ldt', 'exam', 'cito'];
        $dataset = $this->getNationalItemBankDatasource();

        if($dataset->isEmpty()){
            $this->fail('dataset is empty, no valid tcCore\Test records where available in the database');
        }
        $dataset->each(function ($test) use ($validScopes) {
            $this->assertTrue(in_array($test->scope, $validScopes));
        });
    }

    /** @test */
    public function nationalItemBank_dataset_contains_tests_with_all_three_test_scopes_ldt_exam_cito()
    {
        \Auth::login(self::getTeacherOne()); //Auth::User needs access to new testOverview (school_location -> allow_new_test_bank)
        $scopeInDataset = collect([
            'ldt' => false,
            'exam' => false,
            'cito' => false,
        ]);
        $dataset = $this->getNationalItemBankDatasource();

        if($dataset->isEmpty()){
            $this->fail('dataset is empty, no valid tcCore\Test records where available in the database');
        }
        $dataset->each(function ($test) use (&$scopeInDataset) {
            $scopeInDataset[$test->scope] = true;
        });
        $scopeInDataset->each(function ($scopeIsPresentInDataset) {
            $this->assertTrue($scopeIsPresentInDataset);
        });
    }


    /** @test */
    public function can_get_subjects_for_national_item_bank_for_authenticated_user()
    {
        \Auth::login(self::getTeacherOne());

        $subjects = Subject::getSubjectsOfCustomSchoolForUser('TBNI', User::find(1486));

        $this->assertGreaterThan(0, count($subjects));
    }

    /** @test */
    public function can_publish_national_item_bank_test_with_a_correct_abbreviation_and_set_the_correct_scope_with_author_in_NationalItemBankSchool()
    {
        //login using user in NationalItemBank SchoolLocation
        \Auth::login(AuthorsController::getNationalItemBankAuthor());

        //create test that is not yet finished/published (scope => not_ldt)
        $test = $this->createNationalItemBankTest();
        $this->assertNotEquals('ldt', $test->scope);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';
        $test->save();

        //assert 'scope' changed to correct new scope
        $this->assertEquals('ldt', $test->fresh()->scope);
    }


    /** @test */
    public function publishing_national_item_bank_test_sets_the_author_to_the_right_user()
    {
        \Auth::login(AuthorsController::getNationalItemBankAuthor());

        $nationalItemBankValidUser = AuthorsController::getNationalItemBankAuthor();

        $test = $this->createNationalItemBankTest();
        $this->assertNotEquals($nationalItemBankValidUser->username, $test->author->username);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';
        $test->save();

        //assert 'author' changed to correct new author
        $this->assertEquals($nationalItemBankValidUser->username, $test->fresh()->author->username);
    }

    /** @test */
    public function cannot_publish_national_item_bank_test_with_a_correct_abbreviation_with_author_in_different_school()
    {
        //login using teacher in different school
        \Auth::login(self::getTeacherOne());

        $test = $this->createNationalItemBankTest();
        $this->assertNotEquals('ldt', $test->scope);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';
        $test->save();

        //assert 'scope' not changed to correct new scope
        $this->assertNotEquals('ldt', $test->scope);
    }

    /** @test */
    public function cannot_publish_national_item_bank_test_with_a_correct_abbreviation_with_demovak_subject()
    {
        $nationalItemBankUser = AuthorsController::getNationalItemBankAuthor();
        \Auth::login($nationalItemBankUser);

        $demovakSubject = $nationalItemBankUser
            ->schoolLocation
            ->schoolLocationSections[1]
            ->subjects
            ->where('name', 'Demovak')->first();
        $test = $this->createNationalItemBankTest($demovakSubject);
        $this->assertNotEquals('ldt', $test->scope);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';
        $test->save();

        //assert 'scope' not changed to correct new scope
        $this->assertNotEquals('ldt', $test->fresh()->scope);
    }

    /** @test */
    public function can_unpublish_test()
    {
        \Auth::login(AuthorsController::getNationalItemBankAuthor());

        //create published test
        $finishedPublishedTest = $this->createNationalItemBankTest(null, true);
        $this->assertEquals('LDT', $finishedPublishedTest->abbreviation);
        $this->assertEquals('ldt', $finishedPublishedTest->scope);

        $finishedPublishedTest->abbreviation = 'ELSE';
        $finishedPublishedTest->save();

        //assert scope has changed from 'ldt' to 'not_ldt'
        $this->assertEquals('not_ldt', $finishedPublishedTest->fresh()->scope);
    }

    /** @test */
    public function can_unpublish_testQuestions()
    {
        \Auth::login(AuthorsController::getNationalItemBankAuthor());

        //create published test
        $finishedPublishedTest = $this->createNationalItemBankTest(null, true);
        $this->assertEquals('LDT', $finishedPublishedTest->abbreviation);
        $this->assertEquals('ldt', $finishedPublishedTest->scope);

        $finishedPublishedTest->abbreviation = 'ELSE';
        $finishedPublishedTest->save();
        $this->assertNotEquals('ldt', $finishedPublishedTest->fresh()->scope);

        //assert scope of testQuestions has changed from 'ldt' to 'not_ldt'
        $this->assertEquals('not_ldt', $finishedPublishedTest->testQuestions->first()->question->scope);
    }

    private function createNationalItemBankTest(Subject $subject = null, $finished = false) : Test
    {
        //create test in the right schoolLocation
        $teacher = User::where('username', 'Like', 'info+ontwikkelaar-b%')->first();
        $period = $teacher->schoolLocation->schoolYears[0]->periods[0];
        if(!$subject){
            $subject = $teacher->schoolLocation->schoolLocationSections[1]->subjects[0];
        }

        $test = new Test([
            'subject_id'             => $subject->id,
            'education_level_id'     => 1,
            'period_id'              => $period->id,
            'test_kind_id'           => 3,
            'name'                   => 'test toets nationale item bank ' . $subject->name,
            'abbreviation'           => $finished ? 'LDT' : 'NOT_LDT', //not finished: NOT_LDT, if finished: LDT
            'education_level_year'   => 1,
            'status'                 => 1,
            'introduction'           => 'Beste docent,

                                   Dit is de test toets voor de nationale item bank.',
            'shuffle'                => false,
            'is_system_test'         => false,
            'question_count'         => 0,
            'is_open_source_content' => false,
            'demo'                   => false,
            'scope'                  => 'not_ldt', //not finished: not_ldt, if finished: ldt
            'published'              => '1',
        ]);
        $test->setAttribute('author_id', $teacher->id);
        $test->setAttribute('owner_id', $teacher->school_location_id);
        $test->save();

        $user = \Auth::user();

        $testQuestion = FactoryQuestionOpenShort::create();
        $testQuestion->setTestModel($test);
        $testQuestion->store();

        \Auth::login($user); //FactoryQuestion logs-out the user

        $test->refresh();

        return $test;
    }

    private function getNationalItemBankDatasource(){
        $object = (new TestsOverview);
        $object->mount();
        $object->filters = ['national' => []];

        //test private method via reflection:
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('getNationalDatasource');
        $method->setAccessible(true);

        return collect($method->invokeArgs($object, [])->all());
    }
}