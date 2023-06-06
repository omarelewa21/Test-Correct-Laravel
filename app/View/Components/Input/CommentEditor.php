<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;
use tcCore\AnswerFeedback;
use tcCore\Http\Enums\WscLanguage;
use tcCore\Lib\CkEditorComments\User as CommentUser;
use tcCore\User;

class CommentEditor extends RichTextarea
{
    public function __construct(
        string|int              $editorId,
        ?string                 $type = null,
        ?bool                   $disabled = false,
        null|string|int         $questionId = null,
        null|string|WscLanguage $lang = 'nl_NL',
        ?bool                   $allowWsc = false,
        null|string|int         $maxWords = null,
        ?bool                   $maxWordOverride = false,
        ?bool                   $restrictWords = false,
        ?bool                   $textFormatting = true,
        ?bool                   $mathmlFunctions = true,
    ) {
        $this->userId = auth()->user()->uuid;
        $this->users = [
            (array) CommentUser::fromModel(User::find(1486)),
            (array) CommentUser::fromModel(User::find(1485)),
        ];
        $this->commentThreads = AnswerFeedback::getCommentThreadsByAnswerId(265);

        $this->editorProperties[] = 'userId';
        $this->editorProperties[] = 'users';
        $this->editorProperties[] = 'commentThreads';

        parent::__construct(
            $editorId,
            $type,
            $disabled,
            $questionId,
            $lang,
            $allowWsc,
            $maxWords,
            $maxWordOverride,
            $restrictWords,
            $textFormatting,
            $mathmlFunctions,
        );

        //todo....
    }

    public function render()
    {
        return view('components.input.comment-editor');
    }

    protected function getInitMethod()
    {
        return "RichTextEditor.initAnswerFeedback";
    }

}
