<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Test;

class CmsTestsOverview extends TestsOverview
{
    public $testUuid; /*The UUID of the test you're watching the questions for;*/
    public $cmsTestUuid; /*The UUID of test you are editing in the CMS;*/
    public $questionsOfTest; /*The questions in the test you've opened in the testbank*/

    /* Environment settings for testbank via CMS */
    public $usesTileMenu = false;
    public $cardMode = 'cms';
    public $excludeTabs = ['umbrella'];

    public function showQuestionsOfTest($testUuid)
    {
        $this->testUuid = $testUuid;
        return true;
    }

    /* Overrides TestOverview */
    public function render()
    {
        $results = $this->getDatasource();

        return view('livewire.teacher.cms-tests-overview')->layout('layouts.app-teacher')->with(compact(['results']));
    }

    /* Overrides TestOverview */
    protected function tabNeedsDefaultFilters($tab): bool
    {
        return true;
    }

    /* Overrides TestOverview */
    protected function mergeFiltersWithDefaults($tab): void
    {
        $this->filters[$tab] = array_merge($this->filters[$tab], $this->setDefaultFiltersFromTest($tab));
    }

    private function setDefaultFiltersFromTest($tab): array
    {
        $test = Test::whereUuid($this->cmsTestUuid)->first();

        if ($this->isExternalContentTab($tab)) {
            $defaultSubject = ['base_subject_id' => $test->subject()->pluck('base_subject_id')];
        } else {
            $defaultSubject = ['subject_id' => [$test->subject_id]];
        }

        return [
                'education_level_year' => [$test->education_level_year],
                'education_level_id'   => [$test->education_level_id],
            ] + $defaultSubject;

    }

}