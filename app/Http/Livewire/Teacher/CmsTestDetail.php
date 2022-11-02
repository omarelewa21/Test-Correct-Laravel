<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Http\Traits\WithTestAwarenessProperties;

class CmsTestDetail extends TestDetail
{
    use WithTestAwarenessProperties;

    public $mode = 'cms';
    public $cmsTestUuid;

    protected $queryString = [];

    public function mount($uuid)
    {
        parent::mount($uuid);

        $this->addedQuestionIds = $this->getQuestionIdsThatAreAlreadyInTest($this->cmsTestUuid);
    }

    public function handleReferrerActions()
    {

    }
}
