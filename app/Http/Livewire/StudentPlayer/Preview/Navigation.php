<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Question;
use tcCore\Http\Livewire\StudentPlayer\Navigation as AbstractNavigation;

class Navigation extends AbstractNavigation
{
    public $testId;
    public $startTime;

    public $lastQuestionInGroup = [];
    public $groupQuestionIdsForQuestions = [];


    protected $listeners = [
        'redirect-from-closing-a-question' => 'redirectFromClosedQuestion',
        'redirect-from-closing-a-group'    => 'redirectFromClosedGroup',
        'update-nav-with-closed-question'  => 'updateNavWithClosedQuestion',
        'update-nav-with-closed-group'     => 'updateNavWithClosedGroup',
    ];

    public function mount()
    {
        parent::mount();
        $this->startTime = time();
    }

    public function render()
    {
        return view('livewire.student-player.preview.navigation');
    }

    public function toOverview($currentQuestion)
    {
        $canGoAway = parent::toOverview($currentQuestion);
        if ($canGoAway) {
            return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
        }
        return false;
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

    /**
     * @return void
     */
    protected function fillPropertiesByNav(): void
    {
        $questions = Question::find(collect($this->nav)->pluck('id'));

        foreach ($this->nav as $key => $question) {
            $question = $questions->first(function($item) use ($question) {
                return $item->id === $question['id'];
            });

            $this->groupQuestionIdsForQuestions[$question->getKey()] = 0;
            if($question['is_subquestion']) {
                $groupId = $question->getGroupQuestionIdByTest($this->testId);
                $this->groupQuestionIdsForQuestions[$question->getKey()] = $groupId;
                $this->lastQuestionInGroup[$groupId] = $question->getKey();
                $this->closeableGroups[$groupId] = (bool) Question::whereId($groupId)->value('closeable');
            }
        }
        $this->nav = collect($this->nav);

    }
}
