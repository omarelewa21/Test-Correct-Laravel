<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Test;
use tcCore\TestQuestion;

class CmsTestDetail extends TestDetail
{
    public $mode = 'cms';
    public $cmsTestUuid;

    public $questionsInTest = [];

    public function mount($uuid)
    {
        parent::mount($uuid);

        $this->questionsInTest = TestQuestion::whereIn(
                'test_id',
                Test::whereUuid($this->cmsTestUuid)->select('id')
            )
            ->pluck('question_id');
    }

    public function testContainsQuestion($questionId)
    {
        return $this->questionsInTest->contains($questionId);
    }
}
