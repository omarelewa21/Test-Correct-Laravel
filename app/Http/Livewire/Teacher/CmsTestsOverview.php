<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Test;

class CmsTestsOverview extends TestsOverview
{
    public $testUuid;
    public $questionsOfTest;
    public $usesTileMenu = false;
    public $cardMode = 'cms';

    public function showQuestionsOfTest($testUuid)
    {
        $this->testUuid = $testUuid;
        return true;
    }

    public function render()
    {
        $results = $this->getDatasource();

        return view('livewire.teacher.cms-tests-overview')->layout('layouts.app-teacher')->with(compact(['results']));
    }
}