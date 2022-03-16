<?php

namespace tcCore\Http\Livewire\Drawer;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Test;

class Cms extends Component
{
    protected $queryString = ['testId', 'testQuestionId', 'action'];

    /* Querystring parameters*/
    public string $testId = '';
    public string $testQuestionId = '';
    public string $action = '';

    public $testQuestions;

    public function mount()
    {
        $this->testQuestions = Test::whereUuid($this->testId)->first()->testQuestions->sortBy('order');
    }

    public function render()
    {
        return view('livewire.drawer.cms');
    }

    public function showQuestion($testQuestionUuid, $questionUuid, $subQuestion)
    {
        $this->emitTo(
            'teacher.questions.open-short',
            'showQuestion',
            [
                'testQuestionUuid' => $testQuestionUuid,
                'questionUuid'     => $questionUuid,
                'isSubQuestion'    => $subQuestion,
            ]
        );

        $this->testQuestionId = $questionUuid;
    }

    public function addQuestion($type, $subtype)
    {
        $this->emitTo(
            'teacher.questions.open-short',
            'addQuestion',
            ['type' => $type, 'subtype' => $subtype]
        );
    }


    public function getQuestionsInTestProperty()
    {
        return $this->testQuestions->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                $groupQuestion->subQuestions = $groupQuestion->groupQuestionQuestions->map(function ($item) use (
                    $groupQuestion
                ) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    return $item->question;
                });
            }
            return [$testQuestion];
        });
    }


    public function getQuestionNameForDisplay($question)
    {
        if ($question->type === "MultipleChoiceQuestion") {
            return 'question.'.Str::kebab($question->subtype);
        }
        if ($question->type === "OpenQuestion") {
            return 'question.open-long-short';
        }
        return 'question.'.Str::kebab(Str::replaceFirst('Question', '', $question->type));
    }
}
