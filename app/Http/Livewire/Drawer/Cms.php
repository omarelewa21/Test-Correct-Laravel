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

    public $newQuestions = [];

    public function mount()
    {
        $this->testQuestions = Test::whereUuid($this->testId)->first()->testQuestions->sortBy('order');
        $this->newQuestions = $this->newQuestionInfo();
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
                'isSubQuestion'      => $subQuestion,
            ]
        );

        $this->testQuestionId = $questionUuid;
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


    public function newQuestionInfo()
    {
        return [
            'open'   => [
                [
                    'sticker'     => 'question-open',
                    'name'        => __('question.open-long-short'),
                    'description' => __('question.open-long-short_description'),
                ],
                [
                    'sticker'     => 'question-completion',
                    'name'        => __('question.completion'),
                    'description' => __('question.completion_description'),
                ],
                [
                    'sticker'     => 'question-drawing',
                    'name'        => __('question.drawing'),
                    'description' => __('question.drawing_description'),
                ],
            ],
            'closed' => [
                [
                    'sticker'     => 'question-multiple-choice',
                    'name'        => __('question.multiple-choice'),
                    'description' => __('question.multiple-choice_description'),
                ],
                [
                    'sticker'     => 'question-matching',
                    'name'        => __('question.matching'),
                    'description' => __('question.matching_description'),
                ],
                [
                    'sticker'     => 'question-classify',
                    'name'        => __('question.classify'),
                    'description' => __('question.classify_description'),
                ],
                [
                    'sticker'     => 'question-ranking',
                    'name'        => __('question.ranking'),
                    'description' => __('question.ranking_description'),
                ],
                [
                    'sticker'     => 'question-true-false',
                    'name'        => __('question.true-false'),
                    'description' => __('question.true-false_description'),
                ],
                [
                    'sticker'     => 'question-selection',
                    'name'        => __('question.selection'),
                    'description' => __('question.selection_description'),
                ],
                [
                    'sticker'     => 'question-arq',
                    'name'        => __('question.arq'),
                    'description' => __('question.arq_description'),
                ],
            ],
            'extra'  => [
                [
                    'sticker'     => 'question-infoscreen',
                    'name'        => __('question.infoscreen'),
                    'description' => __('question.infoscreen_description'),
                ]
            ]
        ];
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
