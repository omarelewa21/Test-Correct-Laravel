<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use tcCore\Http\Interfaces\QuestionCms;

class CmsFactory
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

        throw new \Exception('CMS Factory could not resolve (sub) question type.');
    }

    /**
     * @return array
     */
    private static function getLookup(): array
    {
        return [
            'InfoscreenQuestion'     => CmsInfoScreen::class,
            'RankingQuestion'        => CmsRanking::class,
            'OpenQuestion'           => [
                'short'   => CmsOpen::class,
                'medium'  => CmsOpen::class,
                'long'    => CmsOpen::class,
                'writing' => CmsWritingAssignment::class,
            ],
            'DrawingQuestion'        => CmsDrawing::class,
            'GroupQuestion'          => CmsGroup::class,
            'MultipleChoiceQuestion' => [
                'truefalse'      => CmsTrueFalse::class,
                'multiplechoice' => CmsMultipleChoice::class,
                'arq'            => CmsArq::class,
            ],
            'CompletionQuestion'     => [
                'multi'      => CmsSelection::class,
                'completion' => CmsCompletion::class,
            ],
            'MatchingQuestion'       => [
                'matching' => CmsMatching::class,
                'classify' => CmsClassify::class,
            ]
        ];
    }

    public static function questionTypes()
    {
        $questionTypes = [
            'open'   => [
                [
                    'sticker'     => 'question-open',
                    'name'        => __('question.openquestionlong'),
                    'description' => __('question.open-long_description'),
                    'type'        => 'OpenQuestion',
                    'subtype'     => 'medium',
                ],
                [
                    'sticker'     => 'question-open',
                    'name'        => __('question.openquestionshort'),
                    'description' => __('question.open-short_description'),
                    'type'        => 'OpenQuestion',
                    'subtype'     => 'short',
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
        if (Auth::user()->schoolLocation->allow_writing_assignment) {
            array_splice($questionTypes['open'], '2', 0, [[
                'sticker'     => 'question-open',
                'name'        => __('question.openquestionwriting'),
                'description' => __('question.open-writing_description'),
                'type'        => 'OpenQuestion',
                'subtype'     => 'writing',
            ]]);
        }
        return $questionTypes;
    }

    public static function findQuestionNameByTypes($type, $subtype)
    {
        return __('question.' . Str::lower($type . ($subtype ?? '')));

//        if ($type === 'GroupQuestion') return __('question.groupquestion');
//
//        $question = collect(CmsFactory::questionTypes())->flatMap(function ($q) {
//            return $q;
//        })->filter(function($q) use ($type, $subtype) {
//            return $q['type'] == $type && $q['subtype'] === $subtype;
//        })->first();
//
//        return $question['name'];
    }


}
