<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Http\Traits\WithTestAwarenessProperties;

class CmsTestDetail extends TestDetail
{
    use WithTestAwarenessProperties;

    public $mode = 'cms';
    public $cmsTestUuid;

    protected $queryString = [];

    protected function getListeners()
    {
        return $this->listeners + ['updateQuestionsInTest'];
    }

    public function mount($uuid)
    {
        parent::mount($uuid);
        $this->setAddedQuestionIdsArray($this->cmsTestUuid);
    }

    public function handleReferrerActions()
    {

    }

    public function updateQuestionsInTest()
    {
        $this->setAddedQuestionIdsArray($this->cmsTestUuid);
    }
}
