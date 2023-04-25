<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Question;

class Navigation extends TCComponent
{
    public $nav;
    public $testId;
    public $q;
    public $queryString = ['q'];
    public $startTime;

    public $lastQuestionInGroup = [];
    public $groupQuestionIdsForQuestions = [];
    public $closeableGroups = [];

    protected $listeners = [
        'redirect-from-closing-a-question' => 'redirectFromClosedQuestion',
        'redirect-from-closing-a-group'    => 'redirectFromClosedGroup',
        'update-nav-with-closed-question'  => 'updateNavWithClosedQuestion',
        'update-nav-with-closed-group'     => 'updateNavWithClosedGroup',
    ];

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);

        $questions = Question::find(collect($this->nav)->pluck('id'));

        foreach ($this->nav as $key => $question) {
            $question = $questions->first(function($item) use ($question) {
                return $item->id === $question['id'];
            });
//            $question = Question::find($question['id']);
            $this->groupQuestionIdsForQuestions[$question->getKey()] = 0;
            if($question['is_subquestion']) {
                $groupId = $question->getGroupQuestionIdByTest($this->testId);
                $this->groupQuestionIdsForQuestions[$question->getKey()] = $groupId;
                $this->lastQuestionInGroup[$groupId] = $question->getKey();
                $this->closeableGroups[$groupId] = (bool) Question::whereId($groupId)->value('closeable');
            }
        }
        $this->nav = collect($this->nav);
        $this->startTime = time();
    }


    public function render()
    {
        return view('livewire.preview.navigation');
    }

    private function getDetailsFirstQuestion()
    {
        return ['data' => ['prev' => false, 'next' => true, 'turnin' => false]];
    }

    private function getDetailsLastQuestion()
    {
        return ['data' => ['prev' => true, 'next' => false, 'turnin' => true]];
    }

    private function getDetailsQuestion()
    {
        return ['data' => ['prev' => true, 'next' => true, 'turnin' => false]];
    }

    public function previousQuestion()
    {
        if ($this->q > 1) {
            $this->goToQuestion($this->q - 1);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);

    }

    public function nextQuestion()
    {
        if ($this->q < $this->nav->count()) {
            $this->goToQuestion($this->q + 1);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function toOverview($currentQuestion)
    {
        $isThisQuestion = $this->nav[$this->q - 1];

        if ($isThisQuestion['group']['closeable'] && !$isThisQuestion['group']['closed']) {
            $this->dispatchBrowserEvent('close-this-group', $currentQuestion);
        } elseif ($isThisQuestion['closeable'] && !$isThisQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', $currentQuestion);
        } else {
            return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
        }
    }

    public function updateQuestionIndicatorColor()
    {
        $newNav = $this->nav->map(function ($item, $key) {
            $q = $this->q;
            if ($key == --$q) {
                $item['answered'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }

    public function checkIfCurrentQuestionIsInfoscreen($question)
    {
        $questionUuid = $this->nav[$question - 1]['uuid'];
        if (Question::whereUuid($questionUuid)->first()->type === 'InfoscreenQuestion') {
            $this->dispatchBrowserEvent('mark-infoscreen-as-seen', $questionUuid);
            $this->updateQuestionIndicatorColor();
        }
    }

    public function goToQuestion($question)
    {
            $this->q = $question;

            $details = $this->getDetailsQuestion();
            if ($this->q == 1) {
                $details = $this->getDetailsFirstQuestion();
            }

            if ($this->q == $this->nav->count()) {
                $details = $this->getDetailsLastQuestion();
            }

            $this->dispatchBrowserEvent('update-footer-navigation', $details);

            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);

    }

    public function redirectFromClosedQuestion($navInfo)
    {
        $this->updateNavWithClosedQuestion($navInfo['closed_question']);
        $this->goToQuestion($navInfo['next_question']);
    }

    public function redirectFromClosedGroup($navInfo)
    {
        $this->updateNavWithClosedGroup($navInfo['closed_group']);
        $this->goToQuestion($navInfo['next_question']);
    }

    public function updateNavWithClosedQuestion($question)
    {
        $newNav = $this->nav->map(function ($item) use ($question) {
            if ($item['id'] == $question) {
                $item['closed'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }

    public function updateNavWithClosedGroup($groupId)
    {
        $newNav = $this->nav->map(function ($item) use ($groupId) {
            if ($item['group']['id'] == $groupId) {
                $item['group']['closed'] = true;
                return $item;
            }
            return $item;
        });

        $this->nav = $newNav;
    }
}
