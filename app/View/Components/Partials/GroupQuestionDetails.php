<?php

namespace tcCore\View\Components\Partials;

use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use tcCore\GroupQuestion;

class GroupQuestionDetails extends Component
{
    public $name;
    public $subject;
    public $authors;
    public $lastUpdated;
    public $attachmentCount;
    public $subQuestions;
    public $totalScore;
    public $uuid;
    public $closeable;

    public function __construct(GroupQuestion $groupQuestion, $testUuid = null)
    {
        $this->name = $groupQuestion->name;
        $this->subject = $groupQuestion->getQuestionInstance()->subject;
        $this->authors = $groupQuestion->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($groupQuestion->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $groupQuestion->attachments()->count();
        $this->subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->totalScore = $groupQuestion->total_score;
        $this->uuid = $groupQuestion->uuid;
        $this->closeable = $groupQuestion->getQuestionInstance()->closeable;

        if ($testUuid) {
            $this->inTest = $groupQuestion->isInTest($testUuid);
        }
    }

    public function render(): View
    {
        return view('components.partials.group-question-details');
    }
}
