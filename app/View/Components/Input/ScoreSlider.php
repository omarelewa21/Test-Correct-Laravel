<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;
use Illuminate\View\View;

class ScoreSlider extends Component
{
    public function __construct(
        public int            $maxScore,
        public null|int|float $score,
        public string         $modelName,
        public bool           $halfPoints = true,
        public bool           $continuousScoreSlider = false,
        public bool           $disabled = false,
        public string         $mode = 'default',
        public bool           $coLearning = false,
    )
    {

        if ($this->halfPoints && $this->maxScore > 7) {
            $this->continuousScoreSlider = true;
        }
        if (!$this->halfPoints && $this->maxScore > 15) {
            $this->continuousScoreSlider = true;
        }
    }

    public function render(): View
    {
        return view('components.input.score-slider');
    }
}
