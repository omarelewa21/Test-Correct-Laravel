<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use tcCore\Attachment;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Interfaces\CmsProvider;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Question;
use tcCore\TestQuestion;

abstract class CmsBase implements CmsProvider
{
    protected $instance;
    public $requiresAnswer = true;

    public function __construct(QuestionCms $instance)
    {
        $this->instance = $instance;
    }

    public function isCarouselGroup()
    {
        return false;
    }

    /**
     * @return mixed|\tcCore\Question
     */
    protected function getQuestion()
    {
        if ($this->instance instanceof OpenShort) {
            if ($this->instance->isPartOfGroupQuestion()) {
                $tq = GroupQuestionQuestion::whereUuid($this->instance->groupQuestionQuestionId)->firstOrFail();
            } else {
                $tq = TestQuestion::whereUuid($this->instance->testQuestionId)->firstOrFail();
            }
            return $tq->question;
        }

        return Question::whereUuid($this->instance->question['uuid'])->first();
    }

    public function getVideoHost($link): ?string
    {
        return Attachment::getVideoHost($link);
    }
}