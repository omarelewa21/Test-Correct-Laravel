<?php

namespace tcCore\Http\Livewire\Question;

use Illuminate\Routing\Route;
use Illuminate\Support\Collection;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Question;
use function Symfony\Component\String\s;

class Navigation extends Component
{
    public $nav;
    public $testTakeUuid;
    public $q;
    public $queryString = ['q'];

    public $showCloseQuestionModal = false;

    public function mount()
    {
        if (!$this->q) {
            $this->q = 1;
        }
        $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
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

    public function updatedQ($value)
    {
        $this->CheckIfCurrentQuestionIsInfoscreen($value);

        $details = $this->getDetailsQuestion();

        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }

        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }

        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function previousQuestion()
    {
        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        if ($this->q > 1) {
            $this->q--;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == 1) {
            $details = $this->getDetailsFirstQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);

    }

    public function nextQuestion()
    {
        $this->checkIfCurrentQuestionIsInfoscreen($this->q);

        if ($this->q < $this->nav->count()) {
            $this->q++;
            $this->dispatchBrowserEvent('current-updated', ['current' => $this->q]);
        }

        $details = $this->getDetailsQuestion();
        if ($this->q == $this->nav->count()) {
            $details = $this->getDetailsLastQuestion();
        }
        $this->dispatchBrowserEvent('update-footer-navigation', $details);
    }

    public function toOverview()
    {
        return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
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
        $questionUuid = $this->nav[--$question]['uuid'];

        if (Question::whereUuid($questionUuid)->first()->type === 'InfoscreenQuestion') {
            $this->emit('changeAnswerUpdatedAt', $questionUuid);
            $this->updateQuestionIndicatorColor();
        }
    }

    public function goToQuestion($question)
    {
        $currentQ = $this->nav[--$this->q];

        if ($currentQ['closeable']) {
            $this->emit('close-question', $currentQ['uuid']);
        } else {
            $this->q = $question;
            $this->dispatchBrowserEvent('current-updated', ['current' => $question]);
        }
    }
}
