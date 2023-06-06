<?php

namespace tcCore\Http\Livewire;

use Ramsey\Uuid\Uuid;
use tcCore\Answer;
use tcCore\AnswerFeedback;
use tcCore\Lib\CkEditorComments\User as CommentUser;
use tcCore\User;

/**
 * PART OF A RESEARCH INTO CKEDITOR 5 COMMENTS PLUGIN
 * TODO: REMOVE IF FOUND LATER ON
 */
class CkEditorCommentsSaveLoad extends TCComponent
{
    public int $answerId = 999;
    public string $answer;

    //Users has to contain at least the id and the name of ALL users that will be shown
    //  at the start only add teacher or
    public $users;
    public $userId;
    protected $commentThreads;
    public $answerModel;

    public function mount()
    {
        $this->answerModel = Answer::find($this->answerId);
//        $this->answer = '<h2>
//                    <comment-start name="thread-1"></comment-start>
//                    Bilingual Personality Disorder
//                    <comment-end name="thread-1"></comment-end>
//                </h2>
//                <p>
//                    <comment-start name="thread-2"></comment-start>This may be the first time<comment-end name="thread-2"></comment-end> you hear about this made-up disorder but it actually isn’t so far from the truth.
//                    As recent studies show, the language you speak has more effects on you than you realize.
//                    According to the studies, the language a person speaks affects their cognition,
//                    behavior, emotions and hence <strong>their personality</strong>.
//                </p>
//                <p>
//                    This shouldn’t come as a surprise
//                    <a href="https://en.wikipedia.org/wiki/Lateralization_of_brain_function">since we already know</a>
//                    that different regions of the brain become more active depending on the activity.
//                    The structure, information and especially <strong>the culture</strong> of languages varies substantially
//                    and the language a person speaks is an essential element of daily life.
//                </p>';
//        $this->answer = '<h2><comment-start name="thread-1"></comment-start>Bilingual Personality Disorder&nbsp;<comment-end name="thread-1"></comment-end></h2><p><comment-start name="thread-2"></comment-start>This may be the first time<comment-end name="thread-2"></comment-end> you hear about this made-up disorder but it actually isn’t so far from the truth. As recent studies show, the language you speak has more effects on you than you realize. According to the studies, the language a person speaks affects their cognition, behavior, emotions and hence <strong>their personality</strong>.</p><p>This shouldn’t come as a surprise since we already know that different regions of the brain become more active depending on the activity. The structure, information and especially <strong>the culture</strong> of languages varies substantially <comment-start name="87bd87df-9808-4b8a-a8ad-9703f5b8f0c1:c0fed"></comment-start>and the language a person speaks is an essential element of daily life.<comment-end name="87bd87df-9808-4b8a-a8ad-9703f5b8f0c1:c0fed"></comment-end></p>';

        $this->answer = json_decode($this->answerModel->json)->value;
//dd($this->answer);

        //id has to be a string
        $this->users = [
            (array) CommentUser::fromModel(User::find(1486)),
            (array) CommentUser::fromModel(User::find(1485)),
        ];
//            [
//                {
//                    id: 'user-1',
//                    name: 'Joe Doe',
//                    teacher: true,
//                    // Note that the avatar is optional.
//                    // avatar: 'https://randomuser.me/api/portraits/thumb/men/26.jpg'
//                },
//                {
//                    id: 'user-2',
//                    name: 'Ella Harper',
//                }
//            ]
    }

    public function render()
    {
        $this->userId = auth()->user()->uuid;
        $this->commentThreads = $this->getCommentThreads();

        return view('livewire.ck-editor-comments-save-load')->layout('layouts.base');
    }

    public function createNewComment()
    {
        //todo create AnswerFeedback + save new Answer text

        $newComment = AnswerFeedback::create([
            'answer_id' => 999,
            'user_id' => auth()->id(),
            'message' => '',
            'thread_id' => Uuid::uuid4(),
            'comment_id' => Uuid::uuid4(),
        ]);

        return ['threadId' => $newComment->thread_id, 'commentId' => $newComment->comment_id];
    }

    public function saveNewComment($data)
    {
        //update answers_feedback data
        $this->updateAnswerFeedback(
            threadId: $data['threadId'],
            commentText: $data['message'],
        );
        //update answer text
        $this->updateAnswer(
            updatedAnswerText: $data['answer']
        );
    }


    public function deleteCommentThread($threadId)
    {
        $result = AnswerFeedback::where('thread_id', $threadId)->delete();

        return $result > 0;
    }

    public function updateExistingComment($data)
    {
        //dont update answer text when just editing the comment values? answer doesnt change.
//        $this->updateAnswer(
//            updatedAnswerText: $data['answer']
//        );

        $this->updateAnswerFeedback(
            threadId: $data['threadId'],
            commentText: $data['message'],
        );
    }

    public function updateAnswerText($answerText)
    {
        //update answer text
        $this->updateAnswer($answerText);
    }

    public function getCommentThreads() {

        return AnswerFeedback::getCommentThreadsByAnswerId($this->answerId);
    }




    public function updateAnswer($updatedAnswerText)
    {
        $updatedAnswerText = str_replace('comment-start', 'commentstart', $updatedAnswerText);
        $updatedAnswerText = str_replace('comment-end', 'commentend', $updatedAnswerText);

        $purifiedAnswerTextJson = json_encode((object) ['value' => clean($updatedAnswerText)]);

        $purifiedAnswerTextJson = str_replace('commentstart', 'comment-start', $purifiedAnswerTextJson);
        $purifiedAnswerTextJson = str_replace('commentend', 'comment-end', $purifiedAnswerTextJson);

        Answer::updateJson($this->answerModel->getKey(), $purifiedAnswerTextJson);
    }

    public function updateAnswerFeedback($threadId, $commentText)
    {
        AnswerFeedback::where('thread_id', '=', $threadId)->update(['message' => $commentText]);
    }

    
}
