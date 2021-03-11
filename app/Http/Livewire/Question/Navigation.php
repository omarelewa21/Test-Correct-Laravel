<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Livewire\Student\TestTake;
use tcCore\Question;
use function Symfony\Component\String\s;

class Navigation extends Component
{
    public $nav;
    public $testTakeUuid;
    public $q;
    public $queryString = ['q'];
    public $startTime;

    public $lastQuestionInGroup = [];

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

        foreach ($this->nav as $key => $q) {
            if ($q['group']['closeable']) {
                $this->lastQuestionInGroup[$q['group']['id']] = $key + 1;
            }
        }
        $this->startTime = time();
    }


    public function render()
    {
        return view('livewire.question.navigation');
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
        $newNav = $this->nav->map(function (&$item, $key) {
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
        $this->doesQuestionExist($question);
        $this->CheckIfCurrentQuestionIsInfoscreen($this->q);

        $isThisQuestion = $this->nav[$this->q - 1];

        $this->registerTimeForQuestion($isThisQuestion);

        if ($this->nav[$question - 1]['group']['id'] != $isThisQuestion['group']['id'] && $isThisQuestion['group']['closeable'] && !$isThisQuestion['group']['closed']) {
            $this->dispatchBrowserEvent('close-this-group', $question);
        } elseif ($isThisQuestion['closeable'] && !$isThisQuestion['closed']) {
            $this->dispatchBrowserEvent('close-this-question', $question);
        } else {
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
        $newNav = $this->nav->map(function (&$item) use ($question) {
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
        $newNav = $this->nav->map(function (&$item) use ($groupId) {
            if ($item['group']['id'] == $groupId) {
                $item['group']['closed'] = true;
                return $item;
            }
            return $item;
        });

        $this->nav = $newNav;
    }

    private function registerTimeForQuestion($question)
    {
        Answer::registerTime(
            $question['answer_id'],
            time() - $this->startTime
        );
        $this->startTime = time();
    }

    private function doesQuestionExist($question)
    {
//        return
    }
}
