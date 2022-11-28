<?php

namespace tcCore\View\Components\Actions;

use Illuminate\Support\Str;
use Livewire\Livewire;

class TestOpenEdit extends TestActionComponent
{
    public $url;

    public function __construct($uuid, $variant = 'icon-button')
    {
        parent::__construct($uuid, $variant);
        $this->generateEditUrl();
    }

    public function generateEditUrl()
    {
        $this->url = route('teacher.question-editor', [
            'testId'     => $this->test->uuid,
            'action'     => 'edit',
            'owner'      => 'test',
            'withDrawer' => 'true',
            'referrer'   => $this->getReferrerRoute(),
        ]);
    }

    private function getReferrerRoute(): string
    {
        $currentUrl = Str::of(Livewire::originalUrl());

        if ($currentUrl->contains(route('teacher.test-detail', false, false))) {
            return 'teacher.test-detail';
        }

        return 'teacher.tests';
    }

    protected function getDisabledValue(): bool
    {
        return !$this->test->canEdit(auth()->user()) || auth()->user()->isValidExamCoordinator();
    }
}
