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
    public $inTest = false;
    public $showPreviewButton;

    public function mount($questionUuid, $testUuid = null, $inTest = false)
    {
        $this->question = Question::whereUuid($questionUuid)->first();
        $this->showPreviewButton = $this->question->hasCmsPreview();
        $this->authors = $this->question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($this->question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $this->question->attachments()->count();

        $q = (new QuestionHelper())->getTotalQuestion($this->question->getQuestionInstance());
        $this->pValues = $q->getQuestionInstance()->getRelation('pValue');
        $this->inTest = $inTest;
    }

    public function render()
    {
        return view('livewire.teacher.question-detail-modal');
    }

    public static function modalMaxWidth(): string
    {
        return 'xl';
    }

    public function addQuestion()
    {
        $this->emitTo(QuestionBank::class, 'addQuestionFromDetail', $this->question->uuid);
        $this->closeModal();
    }

    public function openPreviewMode()
    {
        $this->emit('openModal', 'teacher.question-cms-preview-modal', ['uuid' => $this->question->uuid, 'inTest' => $this->inTest]);
    }
}
