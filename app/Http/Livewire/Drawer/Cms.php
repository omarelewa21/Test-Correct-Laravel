<?php

namespace tcCore\Http\Livewire\Drawer;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Test;

class Cms extends Component
{
    protected $queryString = ['testId', 'testQuestionId', 'groupQuestionQuestionId', 'action', 'owner'];

    /* Querystring parameters*/
    public $testId = '';
    public $testQuestionId = '';
    public $groupQuestionQuestionId = '';
    public $action = '';
    public $owner = '';

    public $groupId;
    public $questionBankActive = false;

    protected function getListeners()
    {
        return [
            'refreshDrawer'              => 'refreshDrawer',
            'refreshSelf'                    => '$refresh',
            'deleteQuestion'             => 'deleteQuestion',
            'deleteQuestionByQuestionId' => 'deleteQuestionByQuestionId',
        ];
    }

    public function render()
    {
        return view('livewire.drawer.cms');
    }

    public function showQuestion($testQuestionUuid, $questionUuid, $subQuestion, $shouldSave = true)
    {
        $this->emitTo(
            'teacher.questions.open-short',
            'showQuestion',
            [
                'testQuestionUuid' => $testQuestionUuid,
                'questionUuid'     => $questionUuid,
                'isSubQuestion'    => $subQuestion,
                'shouldSave'       => $shouldSave,
            ]
        );

        $this->testQuestionId = $testQuestionUuid;
    }

    public function addQuestion($type, $subtype)
    {
        $this->action = 'add';
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
                $groupQuestion->subQuestions = $groupQuestion->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    $item->question->groupQuestionQuestionUuid = $item->uuid;
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

            return 'question.' . Str::kebab($question->subtype);
        }
        if ($question->type === "OpenQuestion") {
            return 'question.open-long-short';
        }
        return 'question.' . Str::kebab(Str::replaceFirst('Question', '', $question->type));
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

    public function deleteQuestion($testQuestionUuid)
    {
        $this->findOutHowToRedirectButFirstExecuteCallback($testQuestionUuid, function () use ($testQuestionUuid) {
            $response = (new TestQuestionsController)->destroy($this->questionsInTest->firstWhere('uuid', $testQuestionUuid));
        });
    }

    public function findOutHowToRedirectButFirstExecuteCallback($testQuestionUuid, $callback = null)
    {
        $redirectAfter = !!($testQuestionUuid == $this->testQuestionId);
        $questionToNavigateTo = null;

        if ($redirectAfter) {
            $previousQuestion = null;
            $returnInNextIteration = false;
            foreach ($this->questionsInTest as $question) {
                if ($questionToNavigateTo) {
                    continue;
                }

                if ($returnInNextIteration) {
                    $questionToNavigateTo = $question;
                }

                if ($previousQuestion == null) {
                    $previousQuestion = $question;
                }

                if ($question->uuid == $testQuestionUuid) {
                    if ($question != $previousQuestion) {
                        $questionToNavigateTo = $previousQuestion;
                    }
                    $returnInNextIteration = true;
                    continue;
                }
                $previousQuestion = $question;
            }

        }

        if (is_callable($callback)) {
            $callback();
        }

        $this->navigateToQuestion($questionToNavigateTo);
        $this->emitSelf('refreshDrawer');
    }

    private function navigateToQuestion($question = null)
    {
        if ($question == null) {
            if ($this->questionsInTest->isEmpty()) {
                return $this->dispatchBrowserEvent('show-empty');
            }
            return true;
        }

        return $this->showQuestion($question->uuid, $question->question->uuid, false, false);
    }

    public function refreshDrawer($arguments = [])
    {
        collect($arguments)->each(function ($item, $key) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        });
        $this->emitSelf('refreshSelf');
    }

    public function deleteQuestionByQuestionId($questionId)
    {
        $testQuestionUuid = $this->questionsInTest->filter(function ($question) use ($questionId) {
            return $question->question_id == $questionId;
        })->first()->uuid;

        $this->deleteQuestion($testQuestionUuid);
    }

    public function deleteSubQuestion($groupQuestionQuestionId, $testQuestionId)
    {
        $this->findOutHowToRedirectButFirstExecuteCallback('abc', function() use ($testQuestionId, $groupQuestionQuestionId) {
            $groupQuestionQuestion = GroupQuestionQuestion::whereUuid($groupQuestionQuestionId)->first();
            $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($testQuestionId);

            $response = (new GroupQuestionQuestionsController)->destroy(
                $groupQuestionQuestionManager,
                $groupQuestionQuestion
            );
        });
    }
}
