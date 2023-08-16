<?php

namespace tcCore\Http\Livewire\CoLearning;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\AnswerFeedback;
use tcCore\AnswerRating;
use tcCore\Events\CommentedAnswerUpdated;
use tcCore\Http\Enums\AnswerFeedbackFilter;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class OpenQuestion extends CoLearningQuestion
{
    public bool $webSpellChecker = false;
    public bool $inlineFeedbackEnabled = false;
    public string $commentMarkerStyles = '';
    public string $answerId;
    public AnswerFeedbackFilter $answerFeedbackFilter = AnswerFeedbackFilter::ALL;
    public string $updatedAtHash;
    public string $testParticipantUuid;

    protected function getListeners()
    {
        return [
            CommentedAnswerUpdated::channelSignature(testParticipantUuid: $this->testParticipantUuid) => 'getUpdatedAnswerText',
        ];
    }

    public function getUpdatedAnswerText()
    {
        $this->answerRating = AnswerRating::find($this->answerRatingId);

        $this->handleGetAnswerData();
    }

    public function render()
    {
        return view('livewire.co-learning.open-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return $this->answered;
    }

    protected function handleGetAnswerData()
    {
        $this->answer = $this->answerRating->answer->commented_answer ?? json_decode($this->answerRating->answer->json)->value ?? '';
        $this->updatedAtHash = md5($this->answerRating->answer->updated_at->format('His'));
    }
}
