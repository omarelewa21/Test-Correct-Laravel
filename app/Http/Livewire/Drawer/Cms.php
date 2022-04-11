<?php

namespace tcCore\Http\Livewire\Drawer;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\GroupQuestionQuestion;
use tcCore\Test;

class Cms extends Component
{
    protected $queryString = ['testId', 'testQuestionId', 'groupQuestionQuestionId', 'action', 'owner'];

    /* Querystring parameters*/
    public  $testId = '';
    public  $testQuestionId = '';
    public  $groupQuestionQuestionId = '';
    public  $action = '';
    public  $owner = '';

//    public $testQuestions;
    public $groupId;
    public $questionBankActive = false;

    protected function getListeners()
    {
        return [
            'refreshDrawer' => '$refresh',
        ];
    }

    public function mount()
    {

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

        $this->testQuestionId = $testQuestionUuid;
    }

    public function addQuestion($type, $subtype)
    {
        $this->emitTo(
            'teacher.questions.open-short',
            'addQuestion',
            ['type' => $type, 'subtype' => $subtype, 'groupId' => $this->groupId]
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
            if ($question->subtype === "ARQ") {
                return 'question.arq';
            }

            return 'question.'.Str::kebab($question->subtype);
        }
        if ($question->type === "OpenQuestion") {
            return 'question.open-long-short';
        }
        return 'question.'.Str::kebab(Str::replaceFirst('Question', '', $question->type));
    }

    public function addGroup()
    {
        $this->addQuestion('GroupQuestion', 'group');
    }

    public function getTestQuestionsProperty()
    {
        return Test::whereUuid($this->testId)
            ->first()
            ->testQuestions
            ->sortBy('order');
    }
}
