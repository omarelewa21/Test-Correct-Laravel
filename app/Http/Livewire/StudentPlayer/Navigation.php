<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Question;

abstract class Navigation extends StudentPlayerQuestion
{
    public $nav;
    public $q;
    public $queryString = ['q'];
    public $isOverview = false;

    public $lastQuestionInGroup = [];

    protected $listeners = [
        'redirect-from-closing-a-question' => 'redirectFromClosedQuestion',
        'redirect-from-closing-a-group'    => 'redirectFromClosedGroup',
        'update-nav-with-closed-question'  => 'updateNavWithClosedQuestion',
        'update-nav-with-closed-group'     => 'updateNavWithClosedGroup',
        'current-question-answered'        => 'updateQuestionIndicatorColor',
    ];

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }

        $this->fillPropertiesByNav();
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
        $this->dispatchBrowserEvent('update-footer-navigation', ['buttons' => $details, 'number' => $this->q]);
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
        $this->dispatchBrowserEvent('update-footer-navigation', ['buttons' => $details, 'number' => $this->q]);
    }

    public function toOverview($currentQuestion)
    {
        $isThisQuestion = $this->nav[$this->q - 1];

        if ($isThisQuestion['group']['closeable'] && !$isThisQuestion['group']['closed']) {
            $this->dispatchBrowserEvent('close-this-group', 'toOverview');
            return false;
        } elseif ($isThisQuestion['closeable'] && !$isThisQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', 'toOverview');
            return false;
        }

        return true;
    }

    public function updateQuestionIndicatorColor($questionNumber = null)
    {
        $questionNumber ??= $this->q;
        $newNav = $this->nav->map(function ($item, $key) use ($questionNumber) {
            if ($key + 1 == $questionNumber) {
                $item['answered'] = true;
                return $item;
            }
            return $item;
        });
        $this->nav = $newNav;
    }

    public function checkIfCurrentQuestionIsInfoscreen($question)
    {
        if($this->nav->has($question - 1)) {
            $questionUuid = $this->nav[$question - 1]['uuid'];
            if (Question::whereUuid($questionUuid)->first()->type === 'InfoscreenQuestion') {
                $this->dispatchBrowserEvent('mark-infoscreen-as-seen', $questionUuid);
                $this->updateQuestionIndicatorColor($question);
            }
        }
    }

    public function goToQuestion($nextQuestion)
    {
        $this->closeOpenAttachmentsIfAny();

        $this->q = $nextQuestion;

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }

        $this->dispatchBrowserEvent('update-footer-navigation', ['buttons' => $details, 'number' => $this->q]);

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

    public function updateNavWithClosedGroup($groupId, $callable = null)
    {
        $newNav = $this->nav->map(function ($item) use ($callable, $groupId) {
            if ($item['group']['id'] == $groupId) {
                $item['group']['closed'] = true;
                if (!is_null($callable) && is_callable($callable)) {
                    $callable($item);
                }
                return $item;
            }
            return $item;
        });

        $this->nav = $newNav;
    }

    public function redirectTo($route)
    {
        return redirect()->to($route);
    }

    /**
     * @return void
     */
    protected function fillPropertiesByNav(): void
    {
        foreach ($this->nav as $key => $q) {
            if ($q['group']['closeable']) {
                $this->lastQuestionInGroup[$q['group']['id']] = $key + 1;
            }
        }
    }

    protected function closeOpenAttachmentsIfAny()
    {
        $this->emit('close-attachment-' . $this->q);
    }
}
