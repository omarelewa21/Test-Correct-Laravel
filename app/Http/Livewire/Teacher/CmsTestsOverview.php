<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Http\Traits\WithAddExistingQuestionFilterSync;
use tcCore\Test;

class CmsTestsOverview extends TestsOverview
{
    use WithAddExistingQuestionFilterSync;

    public $testUuid; /*The UUID of the test you're watching the questions of;*/
    public $cmsTestUuid; /*The UUID of test you are editing in the CMS;*/
    public $questionsOfTest; /*The questions in the test you've opened in the testbank*/

    /* Environment settings for testbank via CMS */
    public $usesTileMenu = false;
    public $cardMode = 'cms';
    public $excludeTabs = ['umbrella'];

    protected string $filterIdentifyingAttribute = 'cmsTestUuid';

    public $inTestBankContext = false;

    protected function getListeners()
    {
        return $this->listeners + [
                'shared-filter-updated' => 'loadSharedFilters'
            ];
    }

    public function showQuestionsOfTest($testUuid): bool
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
    protected function mergeFiltersWithDefaults(): void
    {
        $this->filters = array_merge($this->filters, $this->setDefaultFiltersFromTest());
    }

    private function setDefaultFiltersFromTest(): array
    {
        $test = Test::whereUuid($this->cmsTestUuid)->first();
        return [
//            'education_level_year' => [$test->education_level_year],
//            'education_level_id'   => [$test->education_level_id],
            'base_subject_id'      => $test->subject()->pluck('base_subject_id'),
            'subject_id'           => [$test->subject_id],
        ];
    }

    public function clearFilters(): void
    {
        parent::clearFilters();
        $this->notifySharedFilterComponents();
    }
}