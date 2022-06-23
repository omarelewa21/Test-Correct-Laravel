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
    public $tags;
    public $inTest = false;

    public function __construct($question, $testUuid)
    {
        $this->question = $question;
        $this->authors = $question->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($question->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $question->attachments()->count();

        $this->tags = $this->question->tags;

        if ($testUuid) {
            $this->inTest = $this->question->isInTest($testUuid);
        }
    }

    public function render(): string
    {
        return 'components.grid.question-card';
    }
}
