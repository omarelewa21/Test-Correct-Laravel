<?php

namespace tcCore\Http\Livewire\CoLearning;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\Question;

class DrawingQuestion extends CoLearningQuestion
{
    public $imgSrc = '';
    public $imageDimensions = ['width' => null, 'height' => null];

    public function render()
    {
        return view('livewire.co-learning.drawing-question');
    }

    public function isQuestionFullyAnswered() : bool {
        return true;
    }

    protected function handleGetAnswerData()
    {
        $answer = $this->answerRating->answer;
        if ($answer->json) {
            $this->answer = json_decode($answer->json)->answer;
            $this->additionalText = json_decode($answer->json)->additional_text;
        }
        if (!$this->answered) {
            return;
        }

        $this->setImageDimensions($answer);
        $this->imgSrc = route('student.drawing-question-answer', $answer->uuid);
    }

    private function setImageDimensions($answer)
    {
        if ($this->question->isOldDrawingQuestion()) {
            return;
        }

        $this->imageDimensions = $answer->getViewBoxDimensionsFromSvg();
    }

    public function imageWidth(): string
    {
        return $this->imageDimensions['width'] ? $this->imageDimensions['width'].'px' : '100%';
    }

    public function imageHeight(): string
    {
        return ($this->imageDimensions['height'] ?: '500') . 'px';
    }
}
