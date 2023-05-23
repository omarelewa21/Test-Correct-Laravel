<?php

namespace tcCore\Lib\CkEditorComments;

/**
 * Data transfer object for use with CKeditor5 Comment plugin
 */
class User
{
    public function __construct(
        public string $id, //userId
        public string $name, //userName
        public string $role = 'teacher',
//        public string $avatar = 'https://randomuser.me/api/portraits/thumb/men/26.jpg',
    )
    {
    }

    public static function fromModel(\tcCore\User $user)
    {
        return (array) new static(
            $user->id,
            $user->nameFull,
            $user->isA('teacher') ? 'teacher' : 'student'
        );
    }

}