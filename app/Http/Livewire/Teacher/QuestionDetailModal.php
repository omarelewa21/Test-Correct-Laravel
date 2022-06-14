<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use LivewireUI\Modal\ModalComponent;
use tcCore\Question;

class QuestionDetailModal extends ModalComponent
{
    public $question;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;

    public function mount($questionUuid)
    {
        $this->question = Question::whereUuid($questionUuid)->first();
        $this->authors = $this->question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($this->question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $this->question->attachments()->count();
    }

    public function render()
    {
        return view('livewire.teacher.question-detail-modal');
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }
}