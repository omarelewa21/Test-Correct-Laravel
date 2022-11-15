<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;

class Navigation extends Component
{
    public $nav;
    public $testTakeUuid;
    public $q;
    public $queryString = ['q'];
    public $playerUrl;
    public $useSlider   ;
    public $lastQuestionInGroup = [];
    public $isOverview = true;

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }
        foreach ($this->nav as $key => $q) {
            if ($q['group']['closeable']) {
                $this->lastQuestionInGroup[$q['group']['id']] = $key+1;
            }
        }
    }


    public function render()
    {
        return view('livewire.question.navigation');
    }

    public function goToQuestion($question)
    {
        return redirect()->to($this->playerUrl.'?q='.$question);
    }

}
