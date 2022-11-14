<?php

namespace tcCore\Http\Livewire\CoLearning;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use tcCore\AnswerRating;
use tcCore\Question;

class DrawingQuestion extends CoLearningQuestion
{
    public $imgSrc = '';

    public function render()
    {
        return view('livewire.co-learning.drawing-question');
    }

    public function isQuestionFullyAnswered() : bool {
        return true;
    }

    protected function handleGetAnswerData()
    {
        if ($this->answerRating->answer->json) {
            $this->answer = json_decode($this->answerRating->answer->json)->answer;
            $this->additionalText = json_decode($this->answerRating->answer->json)->additional_text;
        }
        if(!$this->answered){
            return;
        }
        try {
            if($this->handleDrawingQuestionWithPngExtension($this->answerRating->answer)){
                return true;
            }
            $this->handleDrawingQuestionWithoutPngExtension($this->answerRating->answer);
        }catch (\Exception $e){
            Bugsnag::notifyException($e);
            $this->imgSrc = '';
        }
    }

    private function handleDrawingQuestionWithPngExtension($answer) // new Drawing question
    {
        try {
            $png = Storage::get($answer->getDrawingStoragePathPng());
            $this->imgSrc = "data:image/png;base64," . base64_encode($png);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    private function handleDrawingQuestionWithoutPngExtension($answer) // old Drawing question
    {
        $file = Storage::get($answer->getDrawingStoragePath());
        if (substr($file, 0, 4) === '<svg') {
            throw new \Exception(sprintf('answer of old drawing question with id:%d has svg as drawingStoragePath',$answer->getKey()));
        } else {
            $this->imgSrc = "data:image/png;base64," . base64_encode(file_get_contents($file));
        }
    }
}
