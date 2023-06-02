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
    public $closeableGroups = [];



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
        if (parent::toOverview($currentQuestion)) {
            return redirect()->to(route('student.test-take-overview', $this->testTakeUuid));
        }
        return false;
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
