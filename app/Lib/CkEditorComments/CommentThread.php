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
//        public $isFromAdapter,

        //threadId: data.threadId,
        //                            comments: [
        //                                {
        //                                    commentId: 'comment-1',
        //                                    authorId: '1485',
        //                                    content: '<p>Are we sure we want to use a made-up disorder name?</p>',
        //                                    createdAt: new Date(),
        //                                    attributes: {}
        //                                }
        //                            ],
        //                            // It defines the value on which the comment has been created initially.
        //                            // If it is empty it will be set based on the comment marker.
        //                            context: {
        //                                type: 'text',
        //                                value: 'Bilingual Personality Disorder'
        //                            },
        //                            resolvedAt: null,
        //                            resolvedBy: null,
        //                            attributes: {},
        //                            isFromAdapter: true
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
                (array) new Comment($answerFeedback->comment_id, $answerFeedback->user->uuid, $answerFeedback->message, $answerFeedback->updated_at)
            ],
            null,
            null,
            null,
            [],

        );
    }
}