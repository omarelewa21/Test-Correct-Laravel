<?php

namespace tcCore\Http\Livewire\StudentPlayer\Overview;

use tcCore\Http\Livewire\StudentPlayer\Navigation as AbstractNavigation;

class Navigation extends AbstractNavigation
{
    public $testTakeUuid;
    public $playerUrl;
    public $useSlider   ;
    public $lastQuestionInGroup = [];
    public $isOverview = true;


    public function render()
    {
        return view('livewire.student-player.question.navigation');
    }

    public function goToQuestion($nextQuestion)
    {
        return redirect()->to($this->playerUrl.'?q='.$nextQuestion);
    }

}
