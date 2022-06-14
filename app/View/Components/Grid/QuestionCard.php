<?php

namespace tcCore\View\Components\Grid;

use Carbon\Carbon;
use Illuminate\View\Component;

class QuestionCard extends Component
{
    public $question;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;

    public function __construct($question)
    {
        $this->question = $question;
        $this->authors = $question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $question->attachments()->count();
    }

    public function render(): string
    {
        return 'components.grid.question-card';
    }
}
