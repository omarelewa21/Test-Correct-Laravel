<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;

class TestTakeOverview extends Component
{
    const STAGES = ['taken'];
    const TABS = ['taken', 'norm'];

    public string $stage;
    public $openTab = 'taken';

    protected $queryString = ['openTab'];

    public function mount($stage)
    {
        if (!in_array($stage, self::STAGES)) {
            abort(404);
        }
        if (!in_array($this->openTab, self::TABS)) {
            abort(404);
        }

        $this->stage = $stage;
    }
    public function render()
    {
        return view('livewire.teacher.test-take-overview')->layout('layouts.app-teacher');
    }
}