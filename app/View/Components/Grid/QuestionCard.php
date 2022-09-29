<?php

namespace tcCore\View\Components\Grid;

use Carbon\Carbon;
use Illuminate\View\Component;
use Illuminate\View\View;

class QuestionCard extends Component
{
    public $question;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;
    public $tags;
    public $inTest = false;
    public $order;
    public $showQuestionBankAddConfirmation = false;

    public function __construct($question, $order = null, $showQuestionBankAddConfirmation=false)
    {
        $this->question = $question;
        $this->authors = $question->authors->map(function($author) {
            return $author->getFullNameWithAbbreviatedFirstName();
        });
        $this->lastUpdated = Carbon::parse($question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $question->attachments()->count();
        $this->order = $order;
        $this->showQuestionBankAddConfirmation = $showQuestionBankAddConfirmation;
    }

    public function render(): View
    {
        return view('components.grid.question-card');
    }
}
