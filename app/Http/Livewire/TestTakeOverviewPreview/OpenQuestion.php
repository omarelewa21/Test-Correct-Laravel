<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use Illuminate\Support\Str;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];
    public $answer = '';
    public $answered;
    public $question;
    public $number;
    public $answers;
    public $editorId;
    public $wordCount = 0;
    public $showQuestionText;

    public function mount()
    {
        $this->editorId = 'editor_'.$this->question->id;

        $temp = (array) json_decode($this->answers[$this->question->uuid]['answer']);
        if (key_exists('value', $temp)) {
            $this->answer = $temp['value'];
        }
        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        if ($this->question->subtype === 'short') {
            return view('livewire.test_take_overview_preview.open-question');
        }

        return view('livewire.test_take_overview_preview.open-medium-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    public function getWordCountText()
    {
        $wordCount = Str::plainTextWordCount($this->answer);
        $maxWords = $this->question?->max_words;

        return sprintf('<span style="margin-right: 0.25rem;">%s</span>
            <span>%s</span>',
            __('test-take.amount_of_words'),
            is_null($maxWords) ? $wordCount : $wordCount . '/' . $maxWords,
        );
    }
}
