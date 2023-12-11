<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Support\Facades\Storage;
use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, WithCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;
    public $answered;

    public $additionalText;
    public $imgSrc;

    public $showQuestionText;

    public function mount()
    {
        $answer = Answer::where('id', $this->answers[$this->question->uuid]['id'])
            ->where('question_id', $this->question->id)
            ->first();
        if ($answer->json) {
            $this->answer = json_decode($answer->json)->answer;
            $this->additionalText = json_decode($answer->json)->additional_text;
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];
        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
        if(!$this->answered){
            return;
        }
        try {
            if($this->handleDrawingQuestionWithPngExtension($answer)){
                return true;
            }
            $this->handleDrawingQuestionWithoutPngExtension($answer);
        }catch (\Exception $e){
            Bugsnag::notifyException($e);
            $this->imgSrc = '';
        }

    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.drawing-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    private function getStudenAnswerImage(Answer $answer)
    {
        try{
            $file = Storage::get($answer->getDrawingStoragePath());
            if (substr($file, 0, 4) ==='<svg') {
                header('Content-type: image/svg+xml');
                echo $file;
                die;
            }
            return file_get_contents($file);
        }catch (\Exception $e){
            Bugsnag::notifyException($e);
        }
        return '';
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
