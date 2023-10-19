<?php

namespace tcCore\Factories;

use Faker\Factory;
use Faker\Generator;
use tcCore\Factories\Traits\FactoryPropertyDefaults;
use tcCore\Http\Enums\WordType;
use tcCore\User;
use tcCore\Word;
use tcCore\WordList;

class FactoryWordList
{
    use FactoryPropertyDefaults;

    protected User $user;
    public WordList $wordList;

    private Generator $faker;

    public static function create(User $user, array $properties = []): static
    {
        $factory = new static();
        $factory->user = $user;
        $factory->wordList = $factory->buildWordList($properties);
        $factory->faker = Factory::create();

        return $factory;
    }

    public static function createWordList(User $user, array $properties = []): WordList
    {
        return self::create($user, $properties)->wordList;
    }

    private function buildWordList(array $properties): WordList
    {
        [$schoolLocationId, $subjectId, $educationLevelId, $educationLevelYear] = $this->getPropertiesForBuilding(
            $this->user ?? User::find(DEFAULT_TEACHER),
            $properties
        );

        return WordList::build(
            $properties['name'] ?? 'Factory list ' . rand(1000, 9999) . rand(1000, 9999),
            $this->user ?? User::find(DEFAULT_TEACHER),
            $subjectId,
            $educationLevelId,
            $educationLevelYear,
            $schoolLocationId,
        );
    }

    public function addRow(array $words = []): static
    {
        /* Als words leeg is, genereer random woorden */
        if (empty($words)) {
            return $this->addNewRandomWordsToList();
        }

        /* Als words genoeg woorden heeft, vul ze maar gewoon in */
        if (count($words) >= 2) {
            return $this->addGivenWordsToList($words);
        }

        /* Als words maar 1 waarde heeft, vul aan tot 2 */
        return $this->addGivenWordWithRandomAssociateToList($words);
    }

    public function addRows(int $numberOfRows = 5, int $typesPerRow = 2): static
    {
        for ($i = 0; $i < $numberOfRows; $i++) {
            $this->addNewRandomWordsToList($typesPerRow - 1);
        }

        return $this;
    }

    private function addNewRandomWordsToList(int $subjectAssociations = 1): FactoryWordList
    {
        if ($subjectAssociations > 3) {
            throw new \Exception('Cannot add more than the 3 types at this moment');
        }

        $subject = $this->wordList->createWord($this->faker->word, WordType::SUBJECT);

        $types = [WordType::TRANSLATION, WordType::SYNONYM, WordType::DEFINITION];
        for ($i = 0; $i < $subjectAssociations; $i++) {
            $this->wordList->createWord($this->faker->word, $types[$i], $subject);
        }

        return $this;
    }

    private function addGivenWordsToList(array $words): FactoryWordList
    {
        $subjectWord = collect($words)->first(fn($word) => $word->isSubjectWord());
        if (!$subjectWord) {
            /* TODO Als er meedere 'niet-subject' woorden gegeven worden, moeten we die dan genereren?*/
            throw new \Exception('No subject word given for row.');
        }

        collect($words)
            ->whereNot('type', WordType::SUBJECT)
            ->each(function ($word) use ($subjectWord) {
                if ($word->word_id !== $subjectWord->getKey()) {
                    $word->word_id = $subjectWord->getKey();
                    $word->save();
                }
            });
        $this->wordList->addRow($subjectWord);

        return $this;
    }

    /**
     * @param array $words
     * @return $this
     */
    private function addGivenWordWithRandomAssociateToList(array $words): FactoryWordList
    {
        $this->wordList->addRow(
            $this->createNecessaryWordAssociations($words[0])
        );

        return $this;
    }

    private function createNecessaryWordAssociations(Word $word): mixed
    {
        /* Als je gegeven woord een Subject is, maak nieuwe met koppeling naar subject */
        if ($word->isSubjectWord()) {
            if ($word->associations()->doesntExist()) {
                $this->wordList->createWord($this->faker->word, WordType::TRANSLATION, $word);
            }
            return $word;
        }

        /* Als words geen woord met type subject bevat, maak er een aan en koppel de gegeven */
        $subject = Word::build(
            $this->faker->word,
            WordType::SUBJECT,
            $this->wordList->user,
            $this->wordList->subject_id,
            $this->wordList->education_level_id,
            $this->wordList->education_level_year,
            $this->wordList->school_location_id,
        );

        $word->word_id = $subject->getKey();
        $word->save();

        return $subject;
    }
}