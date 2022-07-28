<?php

namespace tcCore\View\Components\Grid;

use Illuminate\View\Component;

class QuestionCardDetail extends Component
{
    public $question;
    public $testQuestion;
    public $authors;
    public $attachmentCount;

    public function __construct($testQuestion)
    {
        $this->testQuestion = $testQuestion;
        $this->question = $testQuestion->question;
        $this->authors = $this->question->getAuthorNamesCollection();
        $this->attachmentCount = $this->question->attachments()->count();
    }

    public function render(): string
    {
        return 'components.grid.question-card-detail';
    }
}
