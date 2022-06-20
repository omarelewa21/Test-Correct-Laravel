<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Question;

class QuestionDetailModal extends ModalComponent
{
    public $question;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;
    public $pValues = [];

    public function mount($questionUuid)
    {
        $this->question = Question::whereUuid($questionUuid)->first();
        $this->authors = $this->question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($this->question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $this->question->attachments()->count();

        $q = (new QuestionHelper())->getTotalQuestion($this->question->getQuestionInstance());
        $this->pValues = $q->getQuestionInstance()->getRelation('pValue');
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