<?php

namespace tcCore\Factories;

use Faker\Factory;
use tcCore\Factories\Traits\FactoryPropertyDefaults;
use tcCore\Http\Enums\WordType;
use tcCore\User;
use tcCore\Word;

class FactoryWord
{
    use FactoryPropertyDefaults;

    public Word $word;

    public static function create(User $user = null, array $properties = [], ?Word $subjectWord = null): static
    {
        $factory = new static();
        $factory->user = $user ?? User::find(DEFAULT_TEACHER);
        $factory->faker = Factory::create();
        [$schoolLocationId, $subjectId, $educationLevelId, $educationLevelYear] = $factory->getPropertiesForBuilding(
            $factory->user ?? User::find(DEFAULT_TEACHER),
            $properties
        );

        $factory->word = Word::build(
            $properties['text'] ?? $factory->faker->word,
            $properties['type'] ?? WordType::SUBJECT,
            $factory->user,
            $subjectId,
            $educationLevelId,
            $educationLevelYear,
            $schoolLocationId,
            $subjectWord?->getKey() ?? null,
        );

        return $factory;
    }

    public static function createWord(User $user = null, array $properties = [], ?Word $subjectWord = null): Word
    {
        return static::create($user, $properties, $subjectWord)->word;
    }

    /**
     * @param Word $subjectWord
     * @return $this
     * @throws \Exception
     */
    public function linkToSubjectWord(Word $subjectWord): static
    {
        if ($this->word->isSubjectWord()) {
            throw new \Exception('Cannot link subject word to another subject word.');
        }

        $this->word->word_id = $subjectWord->getKey();
        $this->word->save();

        return $this;
    }
}