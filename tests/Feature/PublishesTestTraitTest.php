<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use tcCore\Factories\Questions\FactoryQuestionOpenShort;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;

class PublishesTestTraitTest extends TestCase
{
    use DatabaseTransactions;

    public $publish = [
        'CREATHLON'   => [
            'abbreviation' => 'PUBLS',
            'scope'        => 'published_creathlon',
            'toetsen_bakker'=> 'info+creathlonontwikkelaarB@test-correct.nl',
        ],
        'OPENSOURCE1' => [
            'abbreviation' => 'EXAM',
            'scope'        => 'exam',
            'toetsen_bakker'=> 'info+CEdocent-b@test-correct.nl',
        ],
        'TBNI'        => [
            'abbreviation' => 'LDT',
            'scope'        => 'ldt',
            'toetsen_bakker'=> 'info+ontwikkelaar-b@test-correct.nl',
        ],
        'SBON'        => [
            'abbreviation' => 'SBON',
            'scope'        => 'published_olympiade',
            'toetsen_bakker'=> 'info+olympiadeontwikkelaar-B@test-correct.nl',
        ],
    ];
    public $unpublish = [
        'CREATHLON'   => [
            'abbreviation' => 'ELSE',
            'scope'        => 'not_published_creathlon',
            'toetsen_bakker'=> 'info+creathlonontwikkelaarB@test-correct.nl',
        ],
        'OPENSOURCE1' => [
            'abbreviation' => 'ELSE',
            'scope'        => 'not_exam',
            'toetsen_bakker'=> 'info+CEdocent-b@test-correct.nl',
        ],
        'TBNI'        => [
            'abbreviation' => 'ELSE',
            'scope'        => 'not_ldt',
            'toetsen_bakker'=> 'info+ontwikkelaar-b@test-correct.nl',
        ],
        'SBON'        => [
            'abbreviation' => 'ELSE',
            'scope'        => 'not_published_olympiade',
            'toetsen_bakker'=> 'info+olympiadeontwikkelaar-B@test-correct.nl',
        ],
    ];

    /**
     * @test
     * @dataProvider validPublishDataSet
     */
    public function can_publish_test($valid_customerCode, $valid_abbreviation, $valid_scope, $toetsen_bakker_username)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        Auth::login(User::whereUsername($toetsen_bakker_username)->first());

        $test = $this->createTest($valid_customerCode, false);
        $this->assertNotEquals($valid_abbreviation, $test->abbreviation);
        $this->assertEquals('not_' . $valid_scope, $test->scope);
        $test->abbreviation = $valid_abbreviation;
        $test->save();

        $this->assertEquals($valid_abbreviation, $test->fresh()->abbreviation);
        $this->assertEquals($valid_scope, $test->fresh()->scope);
    }

    /**
     * @test
     * @dataProvider validPublishDataSet
     */
    public function can_publish_test_and_change_the_author_to_the_correct_author_in_two_places($valid_customerCode, $valid_abbreviation, $valid_scope, $toetsen_bakker_username)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        Auth::login(User::whereUsername($toetsen_bakker_username)->first());
        $publishedTestsAuthorUsername = AuthorsController::getPublishableAuthorByCustomerCode($valid_customerCode);

        $test = $this->createTest($valid_customerCode, false);
        $this->assertNotEquals($valid_abbreviation, $test->abbreviation);
        $this->assertEquals('not_' . $valid_scope, $test->scope);

        $this->assertCount(1, $test->testAuthors);
        $this->assertEquals($toetsen_bakker_username, $test->testAuthors->first()->user->username);
        $this->assertNotEquals($publishedTestsAuthorUsername, $test->testAuthors->first()->user->username);

        $test->abbreviation = $valid_abbreviation;
        $test->save();

        $this->assertEquals($valid_abbreviation, $test->fresh()->abbreviation);
        $this->assertEquals($valid_scope, $test->fresh()->scope);
    }

    /**
     * @test
     * @dataProvider validUnpublishDataSet
     */
    public function can_unpublish_test($valid_customerCode, $invalid_abbreviation, $invalid_scope, $toetsen_bakker_username)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        Auth::login(AuthorsController::getPublishableAuthorByCustomerCode($valid_customerCode));

        $test = $this->createTest($valid_customerCode, true);
        $this->assertEquals($this->publish[$valid_customerCode]['abbreviation'], $test->abbreviation);
        $this->assertEquals($this->publish[$valid_customerCode]['scope'], $test->scope);

        $test->abbreviation = $invalid_abbreviation; //anything else than PUBLS unpublishes the test
        $test->save();

        $this->assertEquals($invalid_abbreviation, $test->fresh()->abbreviation);
        $this->assertEquals($invalid_scope, $test->fresh()->scope);
    }

    /**
     * @test
     * @dataProvider validPublishDataSet
     */
    public function can_publish_test_questions($valid_customerCode, $valid_abbreviation, $valid_scope)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        Auth::login(AuthorsController::getPublishableAuthorByCustomerCode($valid_customerCode));

        $test = $this->createTest($valid_customerCode, false);
        $this->assertNotEquals($valid_abbreviation, $test->abbreviation);
        $this->assertEquals('not_' . $valid_scope, $test->scope);

        $this->assertEquals('not_' . $valid_scope, $test->testQuestions->first()->question->scope);

        $test->abbreviation = $valid_abbreviation;
        $test->save();

        $this->assertEquals($valid_scope, $test->fresh()->testQuestions->first()->question->scope);
    }

    /**
     * @test
     * @dataProvider validUnpublishDataSet
     */
    public function can_unpublish_test_questions($valid_customerCode, $invalid_abbreviation, $invalid_scope)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        Auth::login(AuthorsController::getPublishableAuthorByCustomerCode($valid_customerCode));

        $test = $this->createTest($valid_customerCode, true);
        $this->assertNotEquals($invalid_abbreviation, $test->abbreviation);
        $this->assertNotEquals($invalid_scope, $test->scope);
        $this->assertEquals($invalid_scope, 'not_' . $test->scope); //invalid scope == valid scope with 'not_' prepended

        $this->assertEquals(substr($invalid_scope, 4), $test->testQuestions->first()->question->scope);

        $test->abbreviation = $invalid_abbreviation;
        $test->save();

        $this->assertEquals($invalid_scope, $test->testQuestions->first()->question->scope);
    }

    /**
     * @test
     * @dataProvider validPublishDataSet
     */
    public function cannot_publish_test_in_wrong_publishable_schoollocation($valid_customerCode, $valid_abbreviation, $valid_scope)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        $wrongCustomerCode = collect($this->publish)
            ->reject(fn($i, $customerCode) => $customerCode == $valid_customerCode)
            ->keys()
            ->random(); //wrong customer code, but it is a publishable school_location
        $wrongAuthTeacher = AuthorsController::getPublishableAuthorByCustomerCode($wrongCustomerCode);
        Auth::login($wrongAuthTeacher);

        $test = $this->createTest($valid_customerCode, false);
        $this->assertNotEquals($valid_abbreviation, $test->abbreviation);
        $this->assertEquals('not_' . $valid_scope, $test->scope);

        $test->abbreviation = $valid_abbreviation;
        $test->save();

        $this->assertEquals($valid_abbreviation, $test->fresh()->abbreviation); //abbreviation still changes
        $this->assertNotEquals($valid_scope, $test->fresh()->scope); //scope stays unchanged
    }

    /**
     * @test
     * @dataProvider validPublishDataSet
     */
    public function cannot_unpublish_test($valid_customerCode, $valid_abbreviation, $valid_scope)
    {
        $this->skipUnavailableCustomerCode($valid_customerCode);

        $wrongCustomerCode = collect($this->publish)
            ->reject(fn($i, $customerCode) => $customerCode == $valid_customerCode)
            ->keys()
            ->random(); //wrong customer code, but it is a publishable school_location
        $wrongAuthTeacher = AuthorsController::getPublishableAuthorByCustomerCode($wrongCustomerCode);

        Auth::login($wrongAuthTeacher);

        $test = $this->createTest($valid_customerCode, true);
        $this->assertEquals($valid_abbreviation, $test->abbreviation);
        $this->assertEquals($valid_scope, $test->scope);

        $test->abbreviation = 'ELSE'; //change to something else to (try to) unpublish
        $test->save();

        $this->assertEquals('ELSE', $test->fresh()->abbreviation); //abbreviation still changes
        $this->assertEquals($valid_scope, $test->fresh()->scope); //scope stays unchanged
    }


    // HELPER FUNCTIONS

    private function createTest($customerCode, bool $published = true): Test
    {

        $teacher = $published
            ? AuthorsController::getPublishableAuthorByCustomerCode($customerCode)
            : User::whereUsername($this->publish[$customerCode]['toetsen_bakker'])->first();

        $abbreviation = $published ? $this->publish[$customerCode]['abbreviation'] : $this->unpublish[$customerCode]['abbreviation'];
        $scope = $published ? $this->publish[$customerCode]['scope'] : $this->unpublish[$customerCode]['scope'];

        $period = $teacher->schoolLocation->schoolYears[0]->periods[0];
        $subject = $teacher->schoolLocation->schoolLocationSections[0]->subjects[0];

        $test = new Test([
            'subject_id'           => $subject->id,
            'education_level_id'   => 1,
            'period_id'            => $period->id,
            'test_kind_id'         => 3,
            'name'                 => 'test toets publishable test ' . $subject->name,
            'abbreviation'         => $abbreviation,
            'education_level_year' => 1,
            'status'               => 1,
            'introduction'         => 'This is a publishable test',
            'shuffle'              => false,
            'is_system_test'       => false,
            'question_count'       => 0,
            'demo'                 => false,
            'scope'                => $scope,
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

    protected function skipUnavailableCustomerCode($customerCode)
    {
        if(AuthorsController::getPublishableAuthorByCustomerCode($customerCode) == null) {
            $this->markTestSkipped('no author available for customer code: '. $customerCode);
        }
        if (isset($this->publish['CREATHLON']) && AuthorsController::getPublishableAuthorByCustomerCode('CREATHLON') == null) {
            unset($this->publish['CREATHLON']);
            unset($this->unpublish['CREATHLON']);
        }
        if (isset($this->publish['OPENSOURCE1']) && AuthorsController::getPublishableAuthorByCustomerCode('OPENSOURCE1') == null) {
            unset($this->publish['OPENSOURCE1']);
            unset($this->unpublish['OPENSOURCE1']);
        }
        if (isset($this->publish['TBNI']) && AuthorsController::getPublishableAuthorByCustomerCode('TBNI') == null) {
            unset($this->publish['TBNI']);
            unset($this->unpublish['TBNI']);
        }
        if (isset($this->publish['SBON']) && AuthorsController::getPublishableAuthorByCustomerCode('SBON') == null) {
            unset($this->publish['SBON']);
            unset($this->unpublish['SBON']);
        }
        return;
    }

    // DATASETS

    /**
     * @array["valid_customerCode" => ["valid_customerCode", "valid_abbreviation", "valid_scope"]]
     */
    public function validPublishDataSet()
    {
        $array = [];
        foreach ($this->publish as $key => $value) {
            $array[$key] = [$key, $value['abbreviation'], $value['scope'], $value['toetsen_bakker']];
        }
        return $array;
    }

    /**
     * @array["valid_customerCode" => ["valid_customerCode", "invalid_abbreviation", "invalid_scope"]]
     */
    public function validUnpublishDataSet()
    {
        $array = [];
        foreach ($this->unpublish as $key => $value) {
            $array[$key] = [$key, $value['abbreviation'], $value['scope'], $value['toetsen_bakker']];
        }
        return $array;
    }



}