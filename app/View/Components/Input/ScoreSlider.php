<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;
use Illuminate\View\View;

class ScoreSlider extends Component
{
    public bool $continuousScoreSlider = false;

    public function __construct(
        public int|float      $maxScore,
        public null|int|float $score,
        public string         $modelName,
        public bool           $halfPoints = true,
        public bool           $disabled = false,
        public string         $mode = 'default',
        public bool           $coLearning = false,
        public null|string    $title = null,
        public                $tooltip = null,
        public bool           $focusInput = false,
        public bool           $hideThumb = false,
    ) {
        $this->setContinuousSliderValue();

        if ($this->title === null) {
            $this->title = __('Score');
        }
    }

    public function render(): View
    {
        return view('components.input.score-slider');
    }

    private function setContinuousSliderValue(): void
    {
        if ($this->mode === 'large') {
            $this->continuousScoreSlider = false;
            return;
        }
        if ($this->mode === 'small') {
            $this->continuousScoreSlider = $this->halfPoints ? $this->maxScore > 5 : $this->maxScore > 10;
            return;
        }

        $this->continuousScoreSlider = $this->halfPoints ? $this->maxScore > 7 : $this->maxScore > 15;
    }
}
