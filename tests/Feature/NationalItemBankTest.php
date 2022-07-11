<?php

namespace Tests\Feature;

use Livewire\Livewire;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use tcCore\Subject;
use tcCore\User;
use Tests\TestCase;

class NationalItemBankTest extends TestCase
{
    //todo test:
    // scope aanpassen, eerst scope voor publishing, na publishing een andere
    // alleen de goede tests worden opgehaald, met de goede scope
    // docent aanpassen, bij publishing, aanpassen naar: info+ontwikkelaar@test-correct.nl
    //

    //todo unit test on Test::NationalItemBank

    /** @test */
    public function can_retrieve_a_valid_dataset()
    {
        \Auth::loginUsingId(1486);

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
    public function can_retrieve_a_valid_dataset1()
    {
        //todo test NationalDataset
        \Auth::loginUsingId(1486);

        $test = Livewire::test(TestsOverview::class);


    }

    /** @test */
    public function has_exam_subjects()
    {
        \Auth::loginUsingId(1486);

        $subjects = Subject::getSubjectsOfCustomSchoolForUser('TBNI', User::find(1486));
        dd($subjects);
    }

}