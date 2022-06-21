<?php

namespace tcCore\Factories;

use tcCore\BaseSubject;
use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\Section;

class FactoryBaseSubject
{
    use RandomCharactersGeneratable;

    public $baseSubject;

    //DO NOT CREATE NEW BASE SUBJECTS
    public static function random() : FactoryBaseSubject
    {
        $factory = new static;

        $factory->baseSubject = BaseSubject::inRandomOrder()->first();

        return $factory;
    }

    public static function getRandom() : BaseSubject
    {
        return BaseSubject::inRandomOrder()->first();
    }

    public static function find(int $baseSubjectId) : BaseSubject
    {
        return BaseSubject::find($baseSubjectId);
    }

    protected function definition(): array
    {
        return [
            'name' => 'factory base subject - '.$this->randomCharacters(4),
        ];
    }
}