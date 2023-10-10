<?php

namespace tcCore\Http\Livewire\Teacher\Cms;

use Illuminate\Support\Str;
use tcCore\Http\Interfaces\QuestionCms;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Arq;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Classify;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Completion;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Drawing;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Group;
use tcCore\Http\Livewire\Teacher\Cms\Providers\InfoScreen;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Matching;
use tcCore\Http\Livewire\Teacher\Cms\Providers\MultipleChoice;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Open;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Ranking;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Relation;
use tcCore\Http\Livewire\Teacher\Cms\Providers\Selection;
use tcCore\Http\Livewire\Teacher\Cms\Providers\TrueFalse;
use tcCore\Http\Livewire\Teacher\Cms\Providers\WritingAssignment;

class TypeFactory
{
    private static $self;

    public static function create(QuestionCms $instance)
    {
        $type = $instance->question['type'];
        $subType = Str::lower($instance->question['subtype']);

        $lookup = self::getLookup();

        if (array_key_exists($type, $lookup)) {
            if (is_array($lookup[$type]) && array_key_exists($subType, $lookup[$type])) {
                return static::$self = new $lookup[$type][$subType]($instance);
            }
            return static::$self = new $lookup[$type]($instance);
        }

        throw new \Exception(
            sprintf('CMS Factory could not resolve (sub) question type (type:%s, subtype: %s).', $type, $subType)
        );
    }

    /**
     * @return array
     */
    private static function getLookup(): array
    {
        return [
            'InfoscreenQuestion'     => InfoScreen::class,
            'RankingQuestion'        => Ranking::class,
            'OpenQuestion'           => Open::class,
            'DrawingQuestion'        => Drawing::class,
            'GroupQuestion'          => Group::class,
            'MultipleChoiceQuestion' => [
                'truefalse'      => TrueFalse::class,
                'multiplechoice' => MultipleChoice::class,
                'arq'            => Arq::class,
            ],
            'CompletionQuestion'     => [
                'multi'      => Selection::class,
                'completion' => Completion::class,
            ],
            'MatchingQuestion'       => [
                'matching' => Matching::class,
                'classify' => Classify::class,
            ],
            'RelationQuestion' => Relation::class
        ];
    }

    public static function questionTypes()
    {
        $questionTypes = [
            'open'   => [
                [
                    'sticker'     => 'question-open',
                    'name'        => __('question.openquestionlong'),
                    'description' => __('question.open_description'),
                    'type'        => 'OpenQuestion',
                    'subtype'     => 'write',
                ],
                [
                    'sticker'     => 'question-completion',
                    'name'        => __('question.completion'),
                    'description' => __('question.completion_description'),
                    'type'        => 'CompletionQuestion',
                    'subtype'     => 'completion',
                ],
                [
                    'sticker'     => 'question-drawing',
                    'name'        => __('question.drawing'),
                    'description' => __('question.drawing_description'),
                    'type'        => 'DrawingQuestion',
                    'subtype'     => 'drawing',
                ],
            ],
            'closed' => [
                [
                    'sticker'     => 'question-relation',
                    'name'        => __('question.relationquestion'),
                    'description' => __('question.relation_description'),
                    'type'        => 'RelationQuestion',
                    'subtype'     => 'relation',
                ],
                [
                    'sticker'     => 'question-multiple-choice',
                    'name'        => __('question.multiple-choice'),
                    'description' => __('question.multiple-choice_description'),
                    'type'        => 'MultipleChoiceQuestion',
                    'subtype'     => 'MultipleChoice',
                ],
                [
                    'sticker'     => 'question-matching',
                    'name'        => __('question.matching'),
                    'description' => __('question.matching_description'),
                    'type'        => 'MatchingQuestion',
                    'subtype'     => 'Matching',
                ],
                [
                    'sticker'     => 'question-classify',
                    'name'        => __('question.classify'),
                    'description' => __('question.classify_description'),
                    'type'        => 'MatchingQuestion',
                    'subtype'     => 'Classify',
                ],
                [
                    'sticker'     => 'question-ranking',
                    'name'        => __('question.ranking'),
                    'description' => __('question.ranking_description'),
                    'type'        => 'RankingQuestion',
                    'subtype'     => 'ranking',
                ],
                [
                    'sticker'     => 'question-true-false',
                    'name'        => __('question.true-false'),
                    'description' => __('question.true-false_description'),
                    'type'        => 'MultipleChoiceQuestion',
                    'subtype'     => 'TrueFalse',
                ],
                [
                    'sticker'     => 'question-selection',
                    'name'        => __('question.selection'),
                    'description' => __('question.selection_description'),
                    'type'        => 'CompletionQuestion',
                    'subtype'     => 'multi',
                ],
                [
                    'sticker'     => 'question-arq',
                    'name'        => __('question.arq'),
                    'description' => __('question.arq_description'),
                    'type'        => 'MultipleChoiceQuestion',
                    'subtype'     => 'ARQ',
                ],
            ],
            'extra'  => [
                [
                    'sticker'     => 'question-infoscreen',
                    'name'        => __('question.infoscreen'),
                    'description' => __('question.infoscreen_description'),
                    'type'        => 'InfoscreenQuestion',
                    'subtype'     => 'Infoscreen',
                ]
            ]
        ];

        return $questionTypes;
    }

    public static function findQuestionNameByTypes($type, $subtype)
    {
        return __('question.' . Str::lower($type . ($subtype ?? '')));
    }
}
