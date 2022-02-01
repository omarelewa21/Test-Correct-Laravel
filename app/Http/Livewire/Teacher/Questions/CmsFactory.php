<?php

namespace tcCore\Http\Livewire\Teacher\Questions;

use Illuminate\Support\Str;

class CmsFactory
{

    private static $self;

    public static function create(OpenShort $instance)
    {
        if (static::$self) {
            return static::$self;
        }

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
            'OpenQuestion'           => CmsOpen::class,
            'DrawingQuestion'        => CmsDrawing::class,
            'MultipleChoiceQuestion' => [
                'truefalse'      => CmsTrueFalse::class,
                'multiplechoice' => CmsMultipleChoice::class,
                'arq'            => CmsArq::class,
            ],
            'CompletionQuestion'     => [
                'multi'      => CmsSelection::class,
                'completion' => CmsCompletion::class,
            ],
            'MatchingQuestion'      => [
                'matching'  => CmsMatching::class,
            ]
        ];
    }
}
