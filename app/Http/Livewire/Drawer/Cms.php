<?php

namespace tcCore\Http\Livewire\Drawer;

use Livewire\Component;
use tcCore\Test;

class Cms extends Component
{
    protected $queryString = ['testId', 'testQuestionId', 'action'];

    /* Querystring parameters*/
    public string $testId = '';
    public string $testQuestionId = '';
    public string $action = '';

    public $testQuestionUuids = [];

    public $newQuestions = [];

    public function mount()
    {
        $this->testQuestionUuids = Test::whereUuid($this->testId)->first()->testQuestions()->pluck('uuid');
        $this->newQuestions = $this->newQuestionInfo();
    }

    public function render()
    {
        return view('livewire.drawer.cms');
    }

    public function showQuestion($questionUuid)
    {
        $this->emitTo('teacher.questions.open-short', 'showQuestion', $questionUuid);

        $this->testQuestionId = $questionUuid;
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
}