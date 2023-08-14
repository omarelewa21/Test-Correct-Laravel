<?php

namespace tcCore\Lib\CkEditorComments;

use tcCore\AnswerFeedback;

/**
 * Data transfer object for use with CKeditor5 Comment plugin
 */
class User
{
    public static function fromModel(\tcCore\User $user)
    {
        return [
            "id"   => $user->uuid,
            "name" => $user->nameFull,
            "role" => $user->isA('teacher') ? 'teacher' : 'student'
        ];
    }

    public static function getByAnswerId($answerId)
    {
        $user = auth()->user();

        return AnswerFeedback::where('answer_id', '=', $answerId)
            ->select('user_id')->distinct()
            ->with(['user', 'user.roles'])
            ->get()
            ->map(function ($feedback) {
                return [
                    "id"   => $feedback->user->uuid,
                    'name' => $feedback->user->nameFull,
                    'role' => $feedback->user->isA('teacher') ? 'teacher' : 'student'
                ];
            })->when(function ($collection) use ($user) {
                //the authenticated user has to be always available in the users data
                return $collection->collect()->where('id', '=', $user->uuid)->isEmpty();
            }, function ($collection) use ($user) {
                $collection->add([
                    "id"   => $user->uuid,
                    'name' => $user->nameFull,
                    'role' => $user->isA('teacher') ? 'teacher' : 'student'
                ]);
            });
    }

}