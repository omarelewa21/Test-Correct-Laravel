<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionMatchingClassify extends FactoryQuestionMatching
{
    public function questionDefinition()
    {
        return "<p>Classify question</p>";
    }

    /**
     * the "right" answer needs to be separated by newlines,
     * correct:
     * "right" => "text\ntext\ntext",
     * "right" => "text
     * text
     * text",
     * incorrect:
     * 'right' => 'text\ntext\ntext'  //single quotes make \n act like a string, not a linebreak
     * "right" => "text
     *      text
     *      text"
     * The last example returns: "        text" as second/third answer!!
     * @return array[]
     */
    public function answerDefinition()
    {
        return [
            [
                "order" => 1,
                "left"  => "car",
                "right" => "wheels\nwindows\nsteering wheel",
            ],
            [
                "order" => 2,
                "left"  => "spaghetti",
                "right" => "meatballs",
            ],
            [
                "order" => 3,
                "left"  => "computer",
                "right" => "monitor
keyboard",
            ],
        ];

    }

    public function questionSubType(): string
    {
        return 'classify';
    }
}