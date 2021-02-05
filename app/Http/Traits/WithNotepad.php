<?php


namespace tcCore\Http\Traits;

trait WithNotepad
{
    public $showNotepad = false;
    public $notepadText;

    public function openNotepad()
    {
        $this->notepadText = session('note_text_'.$this->number, '');
//        dd(session()->all());
        $this->showNotepad = true;
    }

    public function closeNotepad()
    {
        session()->put('note_text_'.$this->number, $this->notepadText);
        $this->showNotepad = false;
    }
}