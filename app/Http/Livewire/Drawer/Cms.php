<?php

namespace tcCore\Http\Livewire\Drawer;

use Illuminate\Support\Facades\DB;
use tcCore\GroupQuestion;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Controllers\GroupQuestionQuestionsController;
use tcCore\Http\Controllers\TestQuestionsController;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Livewire\Teacher\Cms\TypeFactory;
use tcCore\Http\Traits\WithQueryStringSyncing;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Question;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestQuestion;

class Cms extends TCComponent
{
    use WithQueryStringSyncing;
    protected $queryString = [
        'testId',
        'testQuestionId',
        'groupQuestionQuestionId',
        'action',
        'owner',
        'type',
        'subtype',
    ];

    /* Querystring parameters*/
    public $testId = '';
    public $testQuestionId = '';
    public $groupQuestionQuestionId = '';
    public $action = '';
    public $owner = '';
    public $type = '';
    public $subtype = '';

    public $groupId;
    public $questionBankActive = false;
    public $emptyStateActive = false;
    public $emitShowOnInit = false;

    public $newQuestionTypeName = '';

    public $duplicateQuestions;

    public $sliderButtonOptions = [];
    public $sliderButtonSelected = 'questions';
    public $sliderButtonDisabled = false;

    private Subject $subject;

    protected function getListeners()
    {
        return [
            'refreshDrawer'              => 'refreshDrawer',
            'refreshSelf'                => '$refresh',
            'deleteQuestion'             => 'deleteQuestion',
            'deleteQuestionByQuestionId' => 'deleteQuestionByQuestionId',
            'show-empty'                 => 'showEmpty',
            'addQuestionResponse'        => 'addQuestionResponse',
            'newGroupId'                 => 'newGroupId',
        ];
    }

    public function mount()
    {
        $this->setSliderButtonOptions();
        if (blank($this->type) && blank($this->subtype)) {
            if ($this->testQuestions->count() === 0) {
                $this->emptyStateActive = true;
            } else {
                $this->emitShowOnInit = true;
            }
            return true;
        }
        if ($this->action === 'add') {
            $this->setQuestionNameString($this->type, $this->subtype);
        }
    }

    public function booted()
    {
        $test = Test::whereUuid($this->testId)->first();
        $this->duplicateQuestions = $test->getDuplicateQuestionIds();
        $this->subject = $test->subject;
    }

    public function render()
    {
        return view('livewire.drawer.cms');
    }

    public function updateTestItemsOrder($data)
    {
        DB::beginTransaction();
        try {
            $test = Test::whereUuid($this->testId)->first();
            if (!$test) {
                throw new \Exception('test could not be found');
            }
            collect($data)->each(function ($item) use ($test) {
                $question = Question::whereUuid($item['value'])->first();
                if (!$question) {
                    throw new \Exception('question could not be found');
                }
                TestQuestion::where('test_id', $test->getKey())->where('question_id', $question->getKey())->update(['order' => $item['order']]);
            });
            DB::commit();
        } catch (\Throwable $e) {
            logger($e->getMessage());
            DB::rollBack();
            $this->refreshDrawer();
        }
    }

    public function updateGroupItemsOrder($data)
    {
        $group = collect($data)->first();

        $groupQuestion = GroupQuestion::whereUuid($group['value'])->first();
        if (!$groupQuestion) {
            $this->refreshDrawer();
            dd('could not find the group question');
        }
        DB::beginTransaction();
        try {
            collect($group['items'])->each(function ($item) use ($groupQuestion) {
                $question = Question::whereUuid($item['value'])->first();
                if (!$question) {
                    throw new \Exception('question could not be found');
                }
                GroupQuestionQuestion::where('group_question_id', $groupQuestion->getKey())->where('question_id', $question->getKey())->update(['order' => $item['order']]);
            });
            DB::commit();
        } catch (\Throwable $e) {
            logger($e->getMessage());
            DB::rollBack();
            $this->refreshDrawer();
        }
    }

    public function showQuestion($testQuestionUuid, $questionUuid, $subQuestion, $shouldSave = true)
    {
        $this->emitTo(
            'teacher.cms.constructor',
            'showQuestion',
            [
                'testQuestionUuid' => $testQuestionUuid,
                'questionUuid'     => $questionUuid,
                'isSubQuestion'    => $subQuestion,
                'shouldSave'       => $shouldSave,
            ]
        );
    }

    public function addQuestion($type, $subtype)
    {
        $this->emitTo(
            'teacher.cms.constructor',
            'addQuestion',
            [
                'type'       => $type,
                'subtype'    => $subtype,
                'groupId'    => $this->groupId,
                'shouldSave' => true
            ]
        );
    }

    public function addQuestionResponse($args)
    {
        $this->setQuestionNameString($args['type'], $args['subtype']);

        if ($this->emptyStateActive) {
            $this->emptyStateActive = false;
            $this->dispatchBrowserEvent('question-change');
            $this->dispatchBrowserEvent('new-question-added');
        }
        $this->dispatchBrowserEvent('processing-end');
        $this->groupId = null;
    }


    public function getQuestionsInTestProperty()
    {
        return $this->testQuestions->flatMap(function ($testQuestion) {
            $testQuestion->question->loadRelated();
            $testQuestion->question->attachmentCount = $testQuestion->question->attachments()->count();
            if ($testQuestion->question->type === 'GroupQuestion') {
                $groupQuestion = $testQuestion->question;
                $groupQuestion->subQuestions = $groupQuestion->groupQuestionQuestions->map(function ($item) use ($groupQuestion) {
                    $item->question->belongs_to_groupquestion_id = $groupQuestion->getKey();
                    $item->question->groupQuestionQuestionUuid = $item->uuid;
                    $item->question->attachmentCount = $item->question->attachments()->count();
                    return $item->question;
                });
            }
            return [$testQuestion];
        });
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
        $this->emit('questionDeleted', $testQuestionUuid);
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
            if (Test::whereUuid($this->testId)->first()->testQuestions()->count() == 0) {
                $this->showEmpty();
            }
            return true;
        }

        $this->dispatchBrowserEvent('question-change', ['new' => $question->uuid, 'old' => $this->testQuestionId]);
        $this->showQuestion($question->uuid, $question->question->uuid, false, false);
        return true;
    }

    public function refreshDrawer($arguments = [])
    {
        collect($arguments)->each(function ($item, $key) {
            if (property_exists($this, $key)) {
                $this->$key = $item;
            }
        });
//        $this->emitSelf('refreshSelf');
    }

    public function deleteQuestionByQuestionId($questionId)
    {
        $testQuestion = $this->questionsInTest->filter(function ($question) use ($questionId) {
            return $question->question_id == $questionId;
        })->first();

        $this->dispatchBrowserEvent('question-removed');
        $this->deleteQuestion($testQuestion->uuid);
    }

    public function deleteSubQuestion($groupQuestionQuestionId, $testQuestionId)
    {
        if ($this->shouldRedirectFromSubQuestion($groupQuestionQuestionId)) {
            $parentTestQuestion = $this->questionsInTest->where('uuid', $testQuestionId)->first();
            $subQuestions = $parentTestQuestion->question->subQuestions;

            if ($subQuestions->count() > 1) {
                $index = $subQuestions->search(function ($question) use ($groupQuestionQuestionId) {
                    return $question->groupQuestionQuestionUuid === $groupQuestionQuestionId;
                });

                if ($index) {
                    $this->showQuestion($testQuestionId, $subQuestions->get($index - 1)->uuid, true, false);
                } else {
                    $this->showQuestion($testQuestionId, $subQuestions->get($index + 1)->uuid, true, false);
                }
            } else {
                $this->showQuestionByTestQuestion($parentTestQuestion);
            }
        }

        $groupQuestionQuestion = GroupQuestionQuestion::whereUuid($groupQuestionQuestionId)->first();
        $groupQuestionQuestionManager = GroupQuestionQuestionManager::getInstanceWithUuid($testQuestionId);

        (new GroupQuestionQuestionsController)->destroy(
            $groupQuestionQuestionManager,
            $groupQuestionQuestion
        );
    }

    public function showEmpty()
    {
        $this->type = '';
        $this->subtype = '';
        $this->action = 'add';
        $this->emptyStateActive = true;
        $this->emitTo('teacher.cms.constructor', 'showEmpty');
    }

    public function handleCmsInit()
    {
        if ($this->emitShowOnInit) {
            $this->showFirstQuestionOfTest();
        }
    }

    public function showFirstQuestionOfTest()
    {
        if ($this->questionsInTest->isNotEmpty()) {
            $this->showQuestionByTestQuestion($this->questionsInTest->first());
        }
    }

    private function showQuestionByTestQuestion($testQuestion)
    {
        $this->showQuestion($testQuestion->uuid, $testQuestion->question->uuid, $testQuestion->type === 'GroupQuestion', false);
    }

    public function removeDummy()
    {
        if ($this->action !== 'add') {
            return true;
        }
        if ($this->questionsInTest->count() > 0) {
            if ($this->owner === 'group') {
                $testQuestion = $this->questionsInTest->where('uuid', $this->testQuestionId)->first();

                if ($testQuestion->question->subQuestions->count()) {
                    return $this->showQuestion($testQuestion->uuid, $testQuestion->question->subQuestions->reverse()->first()->uuid, true, false);
                }

                return $this->showQuestionByTestQuestion($testQuestion);

            } else {
                return $this->showQuestionByTestQuestion($this->questionsInTest->reverse()->first());
            }
        }
        return $this->showEmpty();
    }

    private function shouldRedirectFromSubQuestion($groupQuestionQuestionId)
    {
        return $this->groupQuestionQuestionId === $groupQuestionQuestionId;
    }

    private function setQuestionNameString($type, $subtype)
    {
        $this->newQuestionTypeName = $subtype === 'group' ? __('cms.group-question') : TypeFactory::findQuestionNameByTypes($type, $subtype);
    }

    public function newGroupId($uuid)
    {
        $this->groupId = $uuid;
    }

    public function duplicateQuestion($questionUuid, $testQuestionUuidForGroupQuestion = null)
    {
        $questionToDuplicate = Question::whereUuid($questionUuid)->firstOrFail();

        try {
            $newQuestion = $questionToDuplicate->duplicate($questionToDuplicate->getAttributes());
            Question::whereId($newQuestion->getKey())->update(['derived_question_id'  => null]);
            $newQuestion->attachToParentInTest($this->testId , $testQuestionUuidForGroupQuestion);
        } catch (\Exception $e) {
            $this->dispatchBrowserEvent('notify', ['message' => __('auth.something_went_wrong'), 'error']);
            return false;
        }

        $this->dispatchBrowserEvent('notify', ['message' => __('general.duplication successful')]);
    }

    private function setSliderButtonOptions()
    {
        $this->sliderButtonOptions = [
            'tests' => __('cms.Toetsenbank'),
            'questions' => __('cms.Vragenbank'),
        ];
    }
}
