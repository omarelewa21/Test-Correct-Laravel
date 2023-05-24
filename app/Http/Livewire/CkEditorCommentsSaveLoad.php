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
//        dd($this);
        $this->commentThreads = $this->getCommentThreads();

        return view('livewire.ck-editor-comments-save-load')->layout('layouts.base');
    }

    public function createNewComment()
    {
        //todo create AnswerFeedback + save new Answer text

        $newComment = AnswerFeedback::make([
            'answer_id' => 999,
            'user_id' => 1486,
            'message' => '',
            'thread_id' => Uuid::uuid4(),
            'comment_id' => Uuid::uuid4(),
        ]);

        return ['threadId' => $newComment->thread_id, 'commentId' => $newComment->comment_id];
    }




    public function deleteCommentThread($threadId)
    {
        $af = AnswerFeedback::where('thread_id', $threadId)->delete();

        return $af > 0;
    }

    public function updateCommentThread($data)
    {
        dd($data);

        $this->updateAnswer(); //allways update answerHtml
        $this->updateAnswerFeedback();
    }

    public function getCommentThreads() {

        return AnswerFeedback::getCommentThreadsByAnswerId($this->answerId);
    }

    public function updateAnswer($value = '')
    {
//TODO: update Answer html text
        // example: purifier doesnt accept hyphens in the tagname
        $value = '<h2><comment-start name="thread-1"></comment-start>Bilingual Personality Disorder&nbsp;<comment-end name="thread-1"></comment-end></h2><p><comment-start name="thread-2"></comment-start>This may be the first time<comment-end name="thread-2"></comment-end> you hear about this made-up disorder but it actually isn’t so far from the truth. As recent studies show, the language you speak has more effects on you than you realize. According to the studies, the language a person speaks affects their cognition, behavior, emotions and hence <strong>their personality</strong>.</p><p>This shouldn’t come as a surprise since we already know that different regions of the brain become more active depending on the activity. The structure, information and especially <strong>the culture</strong> of languages varies substantially <comment-start name="87bd87df-9808-4b8a-a8ad-9703f5b8f0c1:c0fed"></comment-start>and the language a person speaks is an essential element of daily life.<comment-end name="87bd87df-9808-4b8a-a8ad-9703f5b8f0c1:c0fed"></comment-end></p>';

        $value = str_replace('comment-start', 'commentstart', $value);
        $value = str_replace('comment-end', 'commentend', $value);

        $json = json_encode((object) ['value' => clean($value)]);

        $json = str_replace('commentstart', 'comment-start', $json);
        $json = str_replace('commentend', 'comment-end', $json);




        $json = json_encode((object) ['value' => clean($html)]);

        Answer::updateJson($this->answerModel, $json);
    }

    public function updateAnswerFeedback()
    {
        //todo save updated answerFeedback data to the right record
    }

    
}
