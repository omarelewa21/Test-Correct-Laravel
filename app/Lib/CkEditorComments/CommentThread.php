<?php

namespace tcCore\Lib\CkEditorComments;

use tcCore\AnswerFeedback;

class CommentThread
{
    public function __construct(
        public string $threadId, //uuid
        public array $comments, //array => [ new Comment,  ]
        public ?array $context,
        public $resolvedAt,
        public $resolvedBy,
        public $attributes,
    )
    {}

    public static function get($threadId, $commentId = null)
    {
        if(is_array($threadId) && isset($threadId['threadId'])) {
            $threadId = $threadId['threadId'];
        }

        return (array) new static(
            $threadId,
            [
                Comment::get($threadId, $commentId)
            ],
            null,
            null,
            null,
            [],

        );
    }

    public static function getByModel(AnswerFeedback $answerFeedback)
    {

        return (array) new static(
            $answerFeedback->thread_id,
            [
                (array) new Comment($answerFeedback->comment_id, $answerFeedback->user->uuid, $answerFeedback->message, $answerFeedback->created_at)
            ],
            null,
            null,
            null,
            [],

        );
    }
}