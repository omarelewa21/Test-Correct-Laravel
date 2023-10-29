<?php

namespace tcCore\Http\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerFeedback;
use tcCore\AnswerRating;
use tcCore\Events\CommentedAnswerUpdated;
use tcCore\Events\TestTakeOpenForInteraction;
use tcCore\Http\Enums\AnswerFeedbackFilter;
use tcCore\Http\Enums\CommentEmoji;
use tcCore\Http\Enums\CommentMarkerColor;
use tcCore\Http\Livewire\Student\CoLearning;
use tcCore\Http\Livewire\Student\TestReview;
use tcCore\Http\Middleware\AfterResponse;

trait WithInlineFeedback {

    public $answerFeedback;
    public AnswerFeedbackFilter $answerFeedbackFilter;

    public function mountWithInlineFeedback()
    {
        $this->initAnswerFeedbackFilter();

        $this->answerFeedback ??= new Collection();
    }

    public function bootWithInlineFeedback()
    {
        if(!isset($this->answerFeedbackFilter)) {
            $this->initAnswerFeedbackFilter();
        }
    }

    public function bootedWithInlineFeedback()
    {
        if(!$this->currentAnwerIsAvailable()) {
            return;
        }
        if ((property_exists($this, 'headerCollapsed') && !$this->headerCollapsed)) {
            return;
        }
        $this->getSortedAnswerFeedback();
    }

    public function createNewComment($commentData, $createCommentIds = true)
    {
        $newComment = AnswerFeedback::create(array_merge([
            'answer_id' => $this->getCurrentAnswer()->getKey(),
            'user_id' => auth()->id(),
            'message' => '',
            'thread_id' => $createCommentIds ? Uuid::uuid4() : null,
            'comment_id' => $createCommentIds ? Uuid::uuid4() : null,
        ], $commentData));

        if(!$createCommentIds) {
            $this->getSortedAnswerFeedback();
        }

        return ['threadId' => $newComment->thread_id, 'commentId' => $newComment->comment_id, 'uuid' => $newComment->uuid];
    }

    public function saveNewComment($data, $answer)
    {
        $this->updateAnswerFeedbackOrder($answer);

        //update answers_feedback data
        $this->updateAnswerFeedback(
            ...$data
        );
        //update answer text
        $this->updateAnswer(
            answer: $answer
        );

        $this->getSortedAnswerFeedback();

        return $this->getCommentMarkerStylesProperty();
    }


    public function deleteCommentThread($threadId, $answerFeedbackUuid)
    {
        if(!$threadId) {
            $result = AnswerFeedback::whereUuid($answerFeedbackUuid)->delete();
            return $result > 0;
        }

        $result = AnswerFeedback::where('thread_id', $threadId)->delete();

        $this->getSortedAnswerFeedback();

        return $result > 0;
    }

    public function updateExistingComment($data)
    {
        $this->updateAnswerFeedback(...$data); //using named parameter splat operation

        $this->getSortedAnswerFeedback();

        return $this->getCommentMarkerStylesProperty();
    }

    public function updateAnswer($answer)
    {
        $purifiedAnswerText = clean($answer);

        $testParticipants = $this->getTestParticipantsRatingTheCurrentAnswer();

        foreach ($testParticipants as $testParticipant) {
            AfterResponse::$performAction[] = fn() => CommentedAnswerUpdated::dispatch($testParticipant->uuid);
        }

        Answer::whereId($this->getCurrentAnswer()->getKey())->update(['commented_answer' => $purifiedAnswerText]);
    }

    public function updateAnswerFeedback($uuid, $message, $comment_color = null, $comment_emoji = null)
    {
        AnswerFeedback::whereUuid($uuid)->update([
            'message' => $message,
            'comment_color' => $comment_color,
            'comment_emoji' => $comment_emoji,
        ]);
    }

    public function updateCommentColor($data)
    {
        if(!isset($data['threadId']) || !isset($data['color'])) {
            return false;
        }

        AnswerFeedback::where('thread_id', '=', $data['threadId'])
            ->update(['comment_color' => $data['color']]);

        $this->getSortedAnswerFeedback();

        return $this->commentMarkerStyles;
    }

    public function updateCommentEmoji($data)
    {
        if(!isset($data['uuid']) || !isset($data['emoji'])) {
            return false;
        }

        AnswerFeedback::whereUuid($data['uuid'])
            ->update(['comment_emoji' => $data['emoji']]);

        $this->getSortedAnswerFeedback();

        return $this->commentMarkerStyles;
    }

    public function getSortedAnswerFeedback()
    {
        if (!$this->getCurrentAnswer()) {
            return;
        }

        $this->answerFeedback = $this->getCurrentAnswer()->feedback()->with('user')->get()->sortBy(function ($feedback) {
            return $feedback->comment_id !== null;
        })->sortBy(function ($feedback) {
            return $feedback->order;
        })->map(function ($feedback) {
            switch ($this->answerFeedbackFilter) {
                case AnswerFeedbackFilter::STUDENTS:
                    $feedback->visible = $feedback->user->isA('student');
                    break;
                case AnswerFeedbackFilter::TEACHER:
                    $feedback->visible = $feedback->user->isA('teacher');
                    break;
                case AnswerFeedbackFilter::CURRENT_USER:
                    $feedback->visible = $feedback->user_id === auth()->id();
                    break;
                case AnswerFeedbackFilter::ALL:
                default:
                    $feedback->visible = true;
                    break;
            }

            return $feedback;
        });
    }

    public function getCommentMarkerStylesProperty() : string
    {
        if(!isset($this->answerFeedback)) {
            return '';
        }

        $defaultHiddenCommentMarkerStyle = <<<STYLE
                    .ck-comment-marker[data-comment]{
                        --ck-color-comment-marker: transparent;
                        --ck-color-comment-marker-border: transparent;
                        --ck-color-comment-marker-active: transparent;
                        cursor: text;
                    } 
            STYLE;

        return  $this->answerFeedback->reduce(function ($carry, $feedback) {
            if(! $feedback->visible) {
                return $carry;
            }
            return $carry = $carry . <<<STYLE
                span.ck-comment-marker[data-comment="{$feedback->thread_id}"]{
                    --ck-color-comment-marker: {$feedback->getColor(0.4)} !important;
                    --ck-color-comment-marker-border: {$feedback->getColor()} !important;
                    --ck-color-comment-marker-active: {$feedback->getColor(0.4)} !important;
                    cursor: pointer;
                }
            STYLE;

        }, $defaultHiddenCommentMarkerStyle);
    }

    /**
     * Context Menu won't work without this method present.
     */
    public function setContextValues($uuid, $contextData): bool
    {
        return true;
    }


    public function getInlineFeedbackEnabledProperty() : bool
    {
        return $this->getCurrentQuestion()->type === "OpenQuestion";
    }

    public function getIconNameByEmojiValue($emojiValue)
    {
        return CommentEmoji::tryFrom($emojiValue)?->getIconComponentName();
    }
    public function getColorCodeByColorValue($colorName, $opacity = 1)
    {
        return CommentMarkerColor::tryFrom($colorName)?->getRgbColorCode($opacity);
    }

    public function updateAnswerFeedbackOrder($answer)
    {
        $answerFeedback = $this->answerFeedback->whereNotNull('thread_id')->pluck('thread_id');

        $commentMarkerUuids = collect(explode("comment-start name=\"", $answer));
        $commentMarkerUuids->shift();
        $commentMarkerUuids = $commentMarkerUuids->map(function ($uuidContainingString) {
            return explode(':',$uuidContainingString)[0];
        })->unique()->values();

        $subCollectionNew = $commentMarkerUuids->filter(fn($item) => $answerFeedback->contains($item));

        if($subCollectionNew->isEmpty()) {
            return;
        }

        $sequel = $subCollectionNew->reduce(function ($carry, $item, $key) {
                $carry .= <<<SQL
        WHEN `thread_id` = "$item" THEN $key 
SQL;
                return $carry;
            }, "UPDATE `answers_feedback` SET `order` = CASE ") . ' ELSE `order` END';

        DB::statement($sequel);
    }

    public function getEditingCommentData($commentUuid)
    {
        $comment = AnswerFeedback::whereUuid($commentUuid)->first();
        $iconName = CommentEmoji::tryFrom($comment->comment_emoji)?->getIconComponentName() ?? '';

        return ['message' => $comment->message, 'comment_color' => $comment->comment_color, 'iconName' => $iconName];

    }

    public function getHasFeedbackProperty() {
        return $this->answerFeedback->filter->visible->isNotEmpty();
    }

    public function getCurrentAnswer()
    {
        return $this->currentAnswer ?? $this->answerRating?->answer;
    }

    public function currentAnwerIsAvailable() : bool
    {
        if (property_exists($this, 'currentAnswer')) {
            return $this->currentAnswer !== null;
        }
        if (property_exists($this, 'answerRating')) {
            return $this->answerRating?->answer !== null;
        }
        return false;
    }

    /**
     * Get the current question safely from either component. Both components contain the current question in a different property.
     */
    public function getCurrentQuestion()
    {
        return $this->currentQuestion ?? $this->testTake->discussingQuestion;
    }

    /**
     * Get the test take safely from either component. Both components contain the test take in a different property.
     */
    public function getTestTake()
    {
        return $this->testTake ?? $this->testTakeData;
    }

    public function initAnswerFeedbackFilter() : void
    {
        if(static::class === CoLearning::class) {
            $this->answerFeedbackFilter = AnswerFeedbackFilter::CURRENT_USER;
            return;
        }
        $this->answerFeedbackFilter = AnswerFeedbackFilter::ALL;
    }

    public function setAnswerFeedbackFilter(string $filter) : void
    {
        if(AnswerFeedbackFilter::tryFrom($filter)) {
            $this->answerFeedbackFilter = AnswerFeedbackFilter::tryFrom($filter);
            $this->getSortedAnswerFeedback();
        }
    }

    public function getTestParticipantsRatingTheCurrentAnswer(): mixed
    {
        return $this->getTestTake()->testParticipants()
                        ->whereIn('user_id', AnswerRating::where('answer_id', $this->getCurrentAnswer()->getKey())
                                                         ->whereType('STUDENT')
                                                         ->whereNot('user_id', auth()->id())
                                                         ->select('user_id')
                        )
                        ->get();
    }

    public function getVisibleAnswerFeedback()
    {
        return $this->answerFeedback->filter->visible;
    }

    public function getAnswerFeedbackUpdatedStateHash()
    {
        return md5($this->getVisibleAnswerFeedback()->reduce(fn($carry, $answerFeedback) => $carry .= $answerFeedback->updated_at->timestamp, ''));
    }
}