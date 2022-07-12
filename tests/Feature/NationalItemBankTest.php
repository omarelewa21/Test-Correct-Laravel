<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
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
    // scope aanpassen, eerst scope voor publishing, na publishing een andere
    // alleen de goede tests worden opgehaald, met de goede scope
    // docent aanpassen, bij publishing, aanpassen naar: info+ontwikkelaar@test-correct.nl
    //


    /** @test */
    public function can_retrieve_a_valid_dataset()
    {
        \Auth::login(self::getTeacherOne()); //user needs access to new testOverview (school location -> allow_new_test_bank)

        //todo test NationalDataset
        // create false / true tests.
        $object = (new TestsOverview);
        $object->mount();
        $object->filters = ['national' => []];

        //test private method via reflection:
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod('getNationalDatasource');
        $method->setAccessible(true);

        dd($method->invokeArgs($object, [])->all()); //returns the array of the dataset

    }

    /** @test */
    public function can_visit_TestsOverview_and_see_valid_nationalItemBank_items()
    {
        //todo test NationalDataset
        \Auth::login(self::getTeacherOne());

        $test = Livewire::test(TestsOverview::class)
            ->set('openTab', 'national');
        // todo add assertions.
    }

    /** @test */
    public function can_test_test_scope_nationalItemBank()
    {
        \Auth::login($this->getTeacherOne());
        //todo unit test on tcCore\Test - scopeNationalItemBankFiltered


        try {
            $tests = Test::nationalItemBankFiltered()->get();
            $this->assertTrue(true);
        } catch (\Exception $e) {
            $this->assertTrue(false);
        }


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
        \Auth::login(AuthorsController::getNationalItemBankAuthor());

        // create test in correct school
        $user = AuthorsController::getNationalItemBankAuthor();

        $test = Test::where('name', 'LIKE', '%nationale item bank%')->where('scope', 'not_ldt')->first();
        $this->assertNotEquals('ldt', $test->scope);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';

        $test->save();

        //assert 'scope' changed to correct new scope
        $this->assertEquals('ldt', $test->scope);
    }

    /** @test */
    public function cannot_publish_national_item_bank_test_with_a_correct_abbreviation_with_author_in_different_school()
    {
        \Auth::login(self::getTeacherOne());

        // create test in correct school
        $user = AuthorsController::getNationalItemBankAuthor();

        $test = Test::where('name', 'LIKE', '%nationale item bank%')->where('scope', 'not_ldt')->first();
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
        \Auth::login(self::getTeacherOne());

        // create test in correct school
        $user = AuthorsController::getNationalItemBankAuthor();

        $rest = $user->schoolLocation->schoolLocationSections[1]->subjects()->where('name', 'LIKE', '%demo%')->first();

        dd($rest); //todo finish test

        $test = $this->createNationalItemBankTestForSubject();
            //Test::where('name', 'LIKE', '%Demovak%')->where('scope', 'not_ldt')->first();
        $this->assertNotEquals('ldt', $test->scope);

        // finish test by saving with correct abbreviation
        $test->abbreviation = 'LDT';
        $test->save();

        //assert 'scope' not changed to correct new scope
        $this->assertNotEquals('ldt', $test->scope);
    }


    private function createNationalItemBankTestForSubject(Subject $subject)
    {
        $teacher = self::getTeacherOne();
        $period = $teacher->schoolLocation->schoolYears()->dd();

        $test = new Test([
            'subject_id'             => $subject->id,
            'education_level_id'     => 1,
            'period_id'              => $periodLocationA->id,
            'test_kind_id'           => 3,
            'name'                   => 'test toets nationale item bank ' . $subject->name,
            'abbreviation'           => 'NOT_LDT', //not finished: NOT_LDT, if finished: LDT
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

        return $test;
    }
}