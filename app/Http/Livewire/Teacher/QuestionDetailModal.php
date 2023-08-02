<?php

namespace tcCore\Http\Livewire\Teacher;

use Carbon\Carbon;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Question;

class QuestionDetailModal extends TCModalComponent
{
    public $question;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;
    public $pValues = [];
    public $inTest = false;
    public $showPreviewButton;
    public $showQuestionBankAddConfirmation = false;

    public function mount($questionUuid, $inTest = false)
    {
        $this->question = Question::whereUuid($questionUuid)->first();
        $this->showPreviewButton = $this->question->hasCmsPreview();
        $this->authors = $this->question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($this->question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $this->question->attachments()->count();

        $q = (new QuestionHelper())->getTotalQuestion($this->question->getQuestionInstance());
        $this->pValues = $q->getQuestionInstance()->getRelation('pValue');
        $this->inTest = $inTest;

        if($this->question->is_subquestion) {
            $groupQuestion = GroupQuestionQuestion::where('question_id', $this->question->id)->first()->groupQuestion;
            if (!empty($groupQuestion->getQuestionInstance()->question) || $this->attachmentCount > 0) {
                $this->showQuestionBankAddConfirmation = true;
            }
        }

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
        if($this->showQuestionBankAddConfirmation)
        {
            $this->emit('openModal', 'teacher.add-sub-question-confirmation-modal', ['questionUuid' => $this->question->uuid]);
            return;
        }
        $this->emitTo(QuestionBank::class, 'addQuestionFromDetail', $this->question->uuid);
        $this->closeModal();
    }

    public function openPreviewMode()
    {
        $this->emit('openModal', 'teacher.question-cms-preview-modal', ['uuid' => $this->question->uuid, 'inTest' => $this->inTest]);
    }
}
