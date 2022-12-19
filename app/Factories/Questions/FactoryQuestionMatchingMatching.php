<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;

class FactoryQuestionMatchingMatching extends FactoryQuestionMatching
{
    public function questionDefinition()
    {
        return "<p>Combine the matching words</p>";
    }

    public function answerDefinition()
    {
        return [
            [
                "order" => 1,
                "left"  => "car",
                "right" => "wheels",
            ],
            [
                "order" => 2,
                "left"  => "spaghetti",
                "right" => "meatballs",
            ],
            [
                "order" => 3,
                "left"  => "computer",
                "right" => "monitor",
            ],
        ];

    }

    public function questionSubType(): string
    {
        return 'matching';
    }
}