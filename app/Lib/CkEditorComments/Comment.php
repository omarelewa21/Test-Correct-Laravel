<?php

namespace tcCore\Lib\CkEditorComments;

use Ramsey\Uuid\Uuid;

class Comment
{
    public function __construct(
        public string $commentId,
        public string $authorId,
        public string $content,
        public string $createdAt,
        public $attributes = null,
    )
    {

    }

    public static function get($threadId, $commentId = null)
    {
        return (array) new static(
            $commentId ?? Uuid::uuid4(),
            '1485',
            '<p>i am not sure</p>',
            now(),
        );
    }
}