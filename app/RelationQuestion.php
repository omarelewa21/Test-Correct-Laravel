<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Enums\WordType;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Http\Traits\Questions\WithQuestionDuplicating;

class RelationQuestion extends Question implements QuestionInterface
{
    use WithQuestionDuplicating;

    protected $fillable = [
        'uuid',
    ];
    protected $table = 'relation_questions';

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'uuid'       => EfficientUuid::class
    ];

    public function wordLists(): BelongsToMany
    {
        return $this->belongsToMany(
            WordList::class,
            RelationQuestionWord::class,
            'relation_question_id',
            'word_list_id'
        )->distinct();
    }

    public function words(): BelongsToMany
    {
        return $this->belongsToMany(
            Word::class,
            RelationQuestionWord::class,
            'relation_question_id',
            'word_id'
        )
            ->distinct()
            ->withPivot(['word_id', 'word_list_id', 'selected']);
    }

    public function rows(): BelongsToMany
    {
        return $this->belongsToMany(
            Word::class,
            RelationQuestionWord::class,
            'relation_question_id',
            'word_id'
        )
            ->whereNull('words.word_id')
            ->where('words.type', WordType::SUBJECT)
            ->distinct()
            ->withPivot(['word_id', 'word_list_id', 'selected']);
    }

    public function questionWords(): hasMany
    {
        return $this->hasMany(RelationQuestionWord::class)
            ->whereNull('relation_question_word.deleted_at')
            ->with('word');
    }

    public function loadRelated()
    {
        // TODO: Implement loadRelated() method.
    }

    public function canCheckAnswer()
    {
        // TODO: Implement canCheckAnswer() method.
    }

    public function checkAnswer($answer)
    {
        // TODO: Implement checkAnswer() method.
    }

    public function addAnswers($mainQuestion, $answers): void
    {
        $currentAnswers = $this->questionWords;
        $answers = collect($answers);

        $answersToCreate = $this->getAnswersToCreate($answers, $currentAnswers);
        $answersToRemove = $this->getAnswersToRemove($answers, $currentAnswers);
        $answersToUpdate = $this->getAnswersToUpdate($answersToRemove, $answersToCreate);

        $this->createAnswers($answersToCreate);
        $this->removeAnswers($answersToRemove);
        $this->updateAnswers($answersToUpdate, $answers);
    }

    public function wordsToAsk(): Collection
    {
        return $this->words()
            ->wherePivot('selected', true)
            ->get();
    }

    public function selectColumn(WordType $newType): void
    {
        $idsToSelect = [];
        $idsToUnselect = [];
        $this->questionWords->each(function ($questionWord) use ($newType, &$idsToSelect, &$idsToUnselect) {
            if ($questionWord->word->type === $newType) {
                $idsToSelect[] = $questionWord->getKey();
            } else {
                $idsToUnselect[] = $questionWord->getKey();
            }
        });

        RelationQuestionWord::whereIn('id', $idsToSelect)->update(['selected' => true]);
        RelationQuestionWord::whereIn('id', $idsToUnselect)->update(['selected' => false]);
    }

    public function answerForWord(Word $word): Word
    {
        if ($word->isSubjectWord()) {
            return $word->associations
                ->sortBy(fn($association) => $association->type->getOrder())
                ->first();
        }

        return $word->subjectWord;
    }

    public function needsToBeUpdated($request)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        if ($this->isDirtyAnswerOptions($totalData)) {
            return true;
        }
        return parent::needsToBeUpdated($request);
    }

    public function isDirtyAnswerOptions($totalData): bool
    {
        $newAnswers = collect($totalData['answers'])->map(function ($item) {
            return [
                'word_id'      => $item['word_id'],
                'word_list_id' => $item['word_list_id'],
                'selected'     => $item['selected']
            ];
        })->sortBy(fn($item) => $item['word_id']);
        $oldAnswers = $this->questionWords->sortBy(fn($item) => $item->word_id);

        if ($oldAnswers->count() !== $newAnswers->count()) {
            return true;
        }

        $isDirty = false;
        $oldAnswers->each(function ($answer, $key) use (&$isDirty, $newAnswers) {
            if ($answer['word_id'] !== $newAnswers[$key]['word_id']) {
                $isDirty = true;
                return false;
            }
            if ($answer['selected'] !== $newAnswers[$key]['selected']) {
                $isDirty = true;
                return false;
            }
        });

        return $isDirty;
    }

    private function getAnswersToCreate(Collection $proposedAnswers, Collection $currentAnswers): Collection
    {
        return $proposedAnswers->filter(function ($proposal) use ($currentAnswers) {
            return $currentAnswers->doesntContain(function ($answer) use ($proposal) {
                return $proposal['word_id'] === $answer->word_id
                    && $proposal['word_list_id'] === $answer->word_list_id
                    && $proposal['selected'] === $answer->selected;
            });
        });
    }

    private function getAnswersToRemove(Collection $proposedAnswers, Collection $currentAnswers): Collection
    {
        return $currentAnswers
            ->filter(function ($answer) use ($proposedAnswers) {
                return $proposedAnswers->doesntContain(function ($proposal) use ($answer) {
                    return $answer->word_id === $proposal['word_id']
                        && $answer->word_list_id === $proposal['word_list_id']
                        && $answer->selected === $proposal['selected'];
                });
            });
    }

    private function getAnswersToUpdate(Collection $answersToRemove, Collection $answersToCreate): Collection
    {
        return $answersToRemove->filter(function ($answer) use ($answersToCreate) {
            return $answersToCreate->contains(fn($proposal) => $proposal['word_id'] === $answer->word_id
                && $proposal['word_list_id'] === $answer->word_list_id);
        })->each(function ($answer) use ($answersToRemove, $answersToCreate) {
            $answersToCreate->forget(
                $answersToCreate->search(
                    fn($answerToCreate) => $answerToCreate['word_id'] === $answer->word_id
                        && $answerToCreate['word_list_id'] === $answer->word_list_id
                )
            );
            $answersToRemove->forget(
                $answersToRemove->search(
                    fn($answerToRemove) => $answerToRemove->word_id === $answer->word_id
                        && $answerToRemove->word_list_id === $answer->word_list_id
                )
            );
        });
    }

    private function createAnswers(Collection $answers): void
    {
        $answers->each(function ($answer) {
            $link = RelationQuestionWord::make($answer);
            $link->relation_question_id = $this->getKey();
            $link->save();
        });
    }

    private function removeAnswers(Collection $answers): void
    {
        RelationQuestionWord::whereIn('id', $answers->pluck('id'))->delete();
    }

    private function updateAnswers(Collection $answersToUpdate, Collection $answerProposals): void
    {
        $answersToUpdate->each(function ($answer) use ($answerProposals) {
            $proposal = $answerProposals->where('word_id', $answer->word_id)
                ->where('word_list_id', $answer->word_list_id)
                ->first();
            $answer->update(['selected' => $proposal['selected']]);
        });
    }

    public function duplicate(array $attributes, $ignore = null): static
    {
        $question = $this->specificDuplication($attributes, $ignore);

        $question->questionWords()->createMany(
            $this->questionWords()
                ->get()
                ->map(function ($relation) use ($question) {
                    $newRelation = $relation->replicate();
                    $newRelation->relation_question_id = $question->getKey();
                    return $newRelation->toArray();
                })
        );

        return $question;
    }
}
