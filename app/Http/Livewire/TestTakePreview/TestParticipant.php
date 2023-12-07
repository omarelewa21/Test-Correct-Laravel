<?php

namespace tcCore\Http\Livewire\TestTakePreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;

class TestParticipant extends TCComponent
{
    use WithStudentTestTakes;
    use TestTakeNavigationForController;

    public $testParticipant;
    public $data;
    public $answers;
    public $playerUrl;
    public $nav;
    public $uuid;
    public $styling;
    public $current = 1;
    public $studentName;
    public $showQuestionText = false;

    public function mount(\tcCore\TestParticipant $testParticipant,TestTake $testTake)
    {

        $this->testParticipant = $testParticipant;
        $this->studentName = $testParticipant->user->nameFull;
        $this->data = self::getData($testParticipant, $testTake);
        $this->data = $this->sanitizeDataForRemovedAnswers();
        $this->answers = $this->getAnswers($testTake, $this->data, $testParticipant);

        $this->playerUrl = route('student.test-take-laravel', ['test_take' => $testTake->uuid]);

        $this->nav = $this->getNavigationData($this->data, $this->answers);
        $this->uuid = $testTake->uuid;
        $this->styling = $this->getCustomStylingFromQuestions($this->data);
    }

    public function render()
    {
        return view('test-take-overview-bare');
    }

    private function sanitizeDataForRemovedAnswers()
    {
        return $this->data->filter(function ($question) {
            return $this->testParticipant->answers->filter(function ($answer) use ($question) {
                return $answer->question_id == $question->getkey();
            })->contains(function ($answer) use ($question) {
                return $answer->question_id == $question->getKey();
            });
        });
    }
}