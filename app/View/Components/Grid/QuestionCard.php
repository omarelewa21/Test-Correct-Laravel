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

    public function __construct($question)
    {
        $this->question = $question;
        $this->authors = $question->authors->map(function($author) {
            return $author->getFullNameWithAbbreviatedFirstName();
        });
        $this->lastUpdated = Carbon::parse($question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $question->attachments_count;
    }

    public function render(): View
    {
        return view('components.grid.question-card');
    }
}
