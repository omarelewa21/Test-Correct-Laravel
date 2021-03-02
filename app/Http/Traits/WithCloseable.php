<?php


namespace tcCore\Http\Traits;

use tcCore\Answer;
use tcCore\Question;

trait WithCloseable
{
    public $closed;
    public $showCloseQuestionModal = false;
    public $showCloseGroupModal = false;
    public $nextQuestion;

    protected function getListeners()
    {
        return [
            'close-question' => 'closeQuestion',
            'close-group' => 'closeGroup',
            'refreshClosed' => 'refreshClosed'
        ];
    }

    public function mountWithCloseable()
    {
        if ($this->answers[$this->question->uuid]['closed'] || $this->answers[$this->question->uuid]['closed_group']) {
            $this->closed = true;
        }
    }

    public function closeQuestion($nextQuestion = null)
    {
        $this->closed = Answer::whereId($this->answers[$this->question->uuid]['id'])->update(['closed' => 1]);

        if ($nextQuestion) {
            $navInfo = [
                'closed_question' => $this->question->getKey(),
                'next_question'   => $nextQuestion
            ];
            $this->emitTo('question.navigation', 'redirect-from-closing-a-question', $navInfo);
        } else {
            $this->emitTo('question.navigation', 'update-nav-with-closed-question', $this->question->getKey());
        }
    }

    public function closeGroup($nextQuestion = null)
    {
        $groupId = $this->group->id;

        $newAnswers = collect($this->answers)->map(function (&$answer) use ($groupId) {
            if ($answer['group_id'] === $groupId) {
                Answer::whereId($answer['id'])->update(['closed_group' => 1]);
                $answer['closed_group'] = true;

                return $answer;
            }
            return $answer;
        })->toArray();

        $this->answers = $newAnswers;

        $this->emit('refreshClosed');

        if ($nextQuestion) {
            $navInfo = [
                'closed_group' => $this->group->id,
                'next_question'   => $nextQuestion
            ];

            $this->emitTo('question.navigation', 'redirect-from-closing-a-group', $navInfo);
        } else {
            $this->emitTo('question.navigation', 'update-nav-with-closed-group', $this->group->id);
        }
    }

    public function refreshClosed()
    {
        if ($this->answers[$this->question->uuid]['closed'] || $this->answers[$this->question->uuid]['closed_group']) {
            $this->closed = true;
        }
    }
}