<?php

namespace tcCore\Factories\Questions;

use tcCore\Factories\FactoryWordList;
use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\PropertyGetableByName;
use tcCore\User;

class FactoryQuestionRelation extends FactoryQuestion
{
    use DoWhileLoggedInTrait;
    use PropertyGetableByName;

    private array $wordLists = [];
    private const DEFAULT_ROWS = 4;
    private const DEFAULT_TYPES = 2;

    public function questionType(): string
    {
        return 'RelationQuestion';
    }

    protected function definition(): array
    {
        return array_merge(
            parent::definition(),
            [
                "shuffle"         => false,
                "selection_count" => null
            ]
        );
    }

    public function questionDefinition(): string
    {
        return '<p>Accompanying text for the RelationQuestion</p>';
    }

    public function questionSubType(): string
    {
        return 'relation';
    }

    public function answerDefinition()
    {
        return null;
    }

    public function useLists(array $wordLists): static
    {
        $this->wordLists = array_merge($this->wordLists, $wordLists);

        return $this;
    }

    protected function prepareForStore(User $user): void
    {
        if (empty($this->wordLists)) {
            $this->wordLists[] = FactoryWordList::create($user)
                ->addRows(static::DEFAULT_ROWS, static::DEFAULT_TYPES)
                ->wordList;
        }

        $answers = [];
        foreach ($this->wordLists as $list) {
            $answers = array_merge(
                $answers,
                $list->load('words')->words->map(function ($word) use ($list) {
                    return [
                        'word_id'      => $word->getKey(),
                        'word_list_id' => $list->getKey(),
                        'selected'     => $word->isSubjectWord(),
                    ];
                })->toArray()
            );
        }

        $this->setProperties([
            'answers' => $answers
        ]);
    }
}