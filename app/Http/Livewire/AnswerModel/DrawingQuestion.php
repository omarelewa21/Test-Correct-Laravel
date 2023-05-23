<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Helpers\SvgHelper;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class DrawingQuestion extends TCComponent
{
    use WithNotepad, WithCloseable, WithGroups;

    public $question;

    public $number;

    public $drawingModalOpened = false;

    public $answers;

    public $answer;
    public $answered;

    public $additionalText;
    public $pngBase64;

    public function mount()
    {
        $svgHelper = new SvgHelper($this->question['uuid']);
        $this->pngBase64 = base64_encode($svgHelper->getCorrectionModelPNG());
        if(stristr($this->question->answer,'data:image/png;base64,')&&is_null($this->question['zoom_group'])){
            $this->pngBase64 = str_replace('data:image/png;base64,','',$this->question->answer);
        }
        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.answer_model.drawing-question');
    }

}
