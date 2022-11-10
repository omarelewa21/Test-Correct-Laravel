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
            'close-group'    => 'closeGroup',
            'refresh'        => 'render'
        ];
    }

    public function mountWithCloseable()
    {
        if (!empty($this->answers) && ($this->answers[$this->question->uuid]['closed'] || $this->answers[$this->question->uuid]['closed_group'])) {
            $this->closed = true;
        }
    }

    public function closeQuestion($nextQuestion = null)
    {
        if (empty($this->answers)) {
            return;
        }

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
        if (empty($this->answers)) {
            return;
        }

        $groupId = $this->group->id;

        $listOfQToRefresh = [];
        $q = 0;
        $newAnswers = collect($this->answers)->map(function ($answer) use ($groupId,&$listOfQToRefresh, &$q) {
            $q++;
            if ($answer['group_id'] === $groupId) {
                Answer::whereId($answer['id'])->update(['closed_group' => 1]);
                $answer['closed_group'] = true;
                array_push($listOfQToRefresh, $q);
                return $answer;
            }
            return $answer;
        })->toArray();

        $this->dispatchBrowserEvent('refresh-question', $listOfQToRefresh);

        $this->answers = $newAnswers;

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
}