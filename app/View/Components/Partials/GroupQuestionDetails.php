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
    public $inTest;
    public $context;
    public $showQBankAddConfirmation = false;

    public function __construct(GroupQuestion $groupQuestion, $context = 'question-bank')
    {
        $this->name = html_entity_decode($groupQuestion->name);
        $this->subject = $groupQuestion->getQuestionInstance()->subject;
        $this->authors = $groupQuestion->getAuthorNamesCollection();
        $this->lastUpdated = Carbon::parse($groupQuestion->updated_at)->format('d/m/\'y');
        $this->attachmentCount = $groupQuestion->attachments()->count();
        $this->totalScore = $groupQuestion->total_score;
        $this->subQuestions = $groupQuestion->groupQuestionQuestions->sortBy('order');
        $this->uuid = $groupQuestion->uuid;
        $this->closeable = $groupQuestion->getQuestionInstance()->closeable;
        $this->inTest = $groupQuestion->inTest ?? false;
        $this->context = $context;
        if($context === 'question-bank' && ( !empty($groupQuestion->getQuestionInstance()->question) || $this->attachmentCount != 0 )){
            $this->showQBankAddConfirmation = true;
        }
    }

    public function render(): View
    {
        return view('components.partials.group-question-details');
    }
}
