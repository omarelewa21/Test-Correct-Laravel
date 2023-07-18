<?php

namespace tcCore\Http\Traits;

use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerFeedback;
use tcCore\Http\Enums\CommentEmoji;
use tcCore\Http\Enums\CommentMarkerColor;

trait WithInlineFeedback {
    //TODO complete CKeditor comments Feedback implementation:

    /* Inline Feedback comments */
    public $answerFeedback;
    protected $answerFeedbackFilter;

    public function createNewComment($commentData, $createCommentIds = true)
    {
        $newComment = AnswerFeedback::create(array_merge([
            'answer_id' => $this->currentAnswer->getKey(),
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
        //purifier cannot handle comment-start and comment-end tags and breaks the answer text.
        $answer = str_replace('comment-start', 'commentstart', $answer);
        $answer = str_replace('comment-end', 'commentend', $answer);

        $purifiedAnswerText = clean($answer);

        $purifiedAnswerText = str_replace('commentstart', 'comment-start', $purifiedAnswerText);
        $purifiedAnswerText = str_replace('commentend', 'comment-end', $purifiedAnswerText);

        Answer::whereId($this->currentAnswer->getKey())->update(['commented_answer' => $purifiedAnswerText]);
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
        if($this->currentQuestion->type !== 'OpenQuestion') {
            return;
        }

        $this->answerFeedback = $this->currentAnswer->feedback()->with('user')->get()->sortBy(function ($feedback) {
            return $feedback->comment_id !== null;
        })->sortBy(function ($feedback) {
            return $feedback->order;
        })->when(isset($this->answerFeedbackFilter), function ($collection) {
            return $collection->filter($this->answerFeedbackFilter);
        });
    }

    public function getCommentMarkerStylesProperty() : string
    {
        if(!isset($this->answerFeedback)) {
            return '';
        }

        return $this->answerFeedback->reduce(function ($carry, $feedback) {
            return $carry = $carry . <<<STYLE
                .ck-comment-marker[data-comment="{$feedback->thread_id}"]{
                            --ck-color-comment-marker: {$feedback->getColor(0.4)} !important;
                            --ck-color-comment-marker-border: {$feedback->getColor()} !important;
                            --ck-color-comment-marker-active: {$feedback->getColor(0.4)} !important;
                        }
            STYLE;

        }, '');
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
        return $this->currentQuestion->type === "OpenQuestion";
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

        preg_match_all('/(?:comment\-start name="{1})(\S+):|(?:comment\-start name="{1})(\S+)"/m', $answer, $matches);

        $result = collect($matches[1])->mapWithKeys(fn($item, $key) => [$key => $item === '' ? $matches[2][$key] : $item]);

        $sequel = $result->filter(fn($item) => $answerFeedback->contains($item))->reduce(function ($carry, $item, $key) {
                $carry .= <<<SQL
        WHEN `thread_id` = "$item" THEN $key \n
SQL;
                return $carry;
            }, "UPDATE `answers_feedback` SET `order` = CASE \n") . ' ELSE `order` END';

        DB::statement($sequel);
    }

    public function getEditingCommentData($commentUuid)
    {
        $comment = AnswerFeedback::whereUuid($commentUuid)->first();
        $iconName = CommentEmoji::tryFrom($comment->comment_emoji)?->getIconComponentName() ?? '';

        return ['message' => $comment->message, 'comment_color' => $comment->comment_color, 'iconName' => $iconName];

    }

    public function getHasFeedbackProperty() {
        return $this->answerFeedback->isNotEmpty();
    }
}