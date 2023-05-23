<?php

namespace tcCore\Http\Livewire;

use tcCore\Lib\CkEditorComments\CommentThread;
use tcCore\Lib\CkEditorComments\User as CommentUser;
use tcCore\User;

/**
 * PART OF A RESEARCH INTO CKEDITOR 5 COMMENTS PLUGIN
 * TODO: REMOVE IF FOUND LATER ON
 */
class CkEditorComments extends TCComponent
{
    public string $answer;

    //Users has to contain at least the id and the name of ALL users that will be shown
    //  at the start only add teacher or
    public $users;

    public function mount()
    {
        $this->answer = '<h2>
                    <comment-start name="e27d05054fa21bf69e31e084cd634b64e"></comment-start>
                    Bilingual Personality Disorder
                    <comment-end name="e27d05054fa21bf69e31e084cd634b64e"></comment-end>
                </h2>
                <p>
                    <comment-start name="abc"></comment-start>This may be the first time<comment-end name="abc"></comment-end> you hear about this made-up disorder but it actually isn’t so far from the truth.
                    As recent studies show, the language you speak has more effects on you than you realize.
                    According to the studies, the language a person speaks affects their cognition,
                    behavior, emotions and hence <strong>their personality</strong>.
                </p>
                <p>
                    This shouldn’t come as a surprise
                    <a href="https://en.wikipedia.org/wiki/Lateralization_of_brain_function">since we already know</a>
                    that different regions of the brain become more active depending on the activity.
                    The structure, information and especially <strong>the culture</strong> of languages varies substantially
                    and the language a person speaks is an essential element of daily life.
                </p>';


        //id has to be a string
        $this->users = [
            CommentUser::fromModel(User::find(1486)),
            CommentUser::fromModel(User::find(1485)),
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
        return view('livewire.ck-editor-comments')->layout('layouts.base');
    }

    public function addComment($data)
    {
        //[
        //  "threadId" => "e27d05054fa21bf69e31e084cd634b64e",
        //  "commentId" => "e2e5c43eb690b41a864a368f152517cf0",
        //  "content" => "<p>fdsfd</p>",
        //  "attributes" => [],
        //]

        //todo:
        //  save threadId to database
        //
        //  save as json to databsae, or create model...


//        dd($data);
    }

    public function addCommentThread($data)
    {
//        dd($data);
    }

    public function removeComment($data)
    {
        dd($data);
    }

    public function removeCommentThread($data)
    {
        dd($data);
    }

    public function updateComment($data)
    {
        //[▼
        //  "threadId" => "e880c6127c539beb45464a4b918bd6381"
        //  "commentId" => "e857d7ace8b5d7a98cc2467306e71331f"
        //  "content" => "<p>testd</p>"
        //]

//        dd($data);
    }

    public function updateCommentThread($data)
    {
        dd($data);
    }

    public function getCommentThread($data)
    {
        //$data:
        //[▼
        //  "threadId" => "thread-1"
        //]

        return CommentThread::get($data['threadId']);


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
    }

    
}
