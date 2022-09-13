<?php

namespace tcCore\View\Components\Actions;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use Livewire\Livewire;
use tcCore\Test;

class TestOpenEdit extends Component
{
    public $test;
    public $variant;
    public $url;
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct($uuid, $variant='icon-button')
    {
        $this->test = Test::findByUuid($uuid);
        $this->variant = $variant;

        $this->generateEditUrl();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('components.actions.test-open-edit');
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
}
