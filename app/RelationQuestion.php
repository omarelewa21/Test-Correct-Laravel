<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Enums\WordType;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Http\Traits\Questions\WithQuestionDuplicating;
use tcCore\Services\CompileWordListService;

class RelationQuestion extends Question implements QuestionInterface
{
    use WithQuestionDuplicating;

    protected $fillable = [
        'uuid',
        'shuffle',
        'selection_count',
        'shuffle_per_participant',
        'auto_check_incorrect_answer',
        'auto_check_answer_case_sensitive',
    ];
    protected $table = 'relation_questions';

    protected $casts = [
        'created_at'                       => 'datetime',
        'updated_at'                       => 'datetime',
        'deleted_at'                       => 'datetime',
        'auto_check_incorrect_answer'      => 'boolean',
        'auto_check_answer_case_sensitive' => 'boolean',
        'uuid'                             => EfficientUuid::class
    ];

    public function question()
    {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function wordLists(): BelongsToMany
    {
        return $this->belongsToMany(
            WordList::class,
            RelationQuestionWord::class,
            'relation_question_id',
            'word_list_id'
        )
            ->whereNull('relation_question_word.deleted_at')
            ->distinct();
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

    public function shuffleCarouselPerTestTake(): bool
    {
        return $this->shuffle && !$this->shuffle_per_participant;
    }

    public function shuffleCarouselPerTestParticipant(): bool
    {
        return $this->shuffle && $this->shuffle_per_participant;
    }

    public function questionWords(): hasMany
    {
        return $this->hasMany(RelationQuestionWord::class)
            ->whereNull('relation_question_word.deleted_at')
            ->with('word');
    }

    public function loadRelated()
    {
        // TODO: Implement loadRelated() method properly.
//        $this->load(['words', 'wordLists', 'testTakeRelationQuestions']);
    }

    /**
     * if 'auto_check_incorrect_answer' is true, the answer can be checked completely.
     * if it is false, the teacher still needs to check the answers that were not (completely) correct.
     * @return bool
     */
    public function canCreateSystemRatingForAnswer($answer): bool
    {
        if((bool)$this->getAttribute('auto_check_incorrect_answer')) {
            return true;
        }
        $this->checkAnswerSub($answer);
        return $answer->allAnswerFieldsCorrect;
    }

    public function checkAnswer($answer)
    {
        if(
            isset($answer->allAnswerFieldsCorrect)
            && $answer->allAnswerFieldsCorrect
            && isset($answer->allAnswerFieldsCorrectScore)
        ) {
            $score = $answer->allAnswerFieldsCorrectScore;
        } else {
            $score = $this->checkAnswerSub($answer);
        }

        return $this->getAttribute('decimal_score') == true
            ? floor($score * 2) / 2
            : floor($score);
    }

    protected function checkAnswerSub($answer)
    {
        //Student answer:
        $answers = collect(json_decode($answer->getAttribute('json'), true));

        //correct answers:
        $answerModel = $this->wordsToAsk()
            ->filter(fn($word) => $answers->has($word->id))
            ->keyBy('id')
            ->map->correctAnswerWord()
            ->map->text;

        $answerOptionsCount = count($answerModel);

        $correctAnswersCount = $answers->reduce(function ($carry, $answer, $key) use ($answerModel, $answers){
            if($answer === null || $answer === '') {
                return $carry;
            }

            return QuestionHelper::compareTextAnswers(
                answerToCheck     : $answer,
                correctAnswers    : $answerModel[$key],
                checkCaseSensitive: $this->getAttribute('auto_check_answer_case_sensitive')
            ) ? ++$carry : $carry;
        }, 0);

        $answer->allAnswerFieldsCorrect = count($answers->filter()) == $correctAnswersCount;
        $answer->allAnswerFieldsCorrectScore = $this->getAttribute('score') * ($correctAnswersCount / $answerOptionsCount);

        return $this->getAttribute('score') * ($correctAnswersCount / $answerOptionsCount);
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
        return $word->correctAnswerWord();
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
        if(!isset($totalData['answers'])) {
            return false;
        }

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
            if ($answer['word_list_id'] !== $newAnswers[$key]['word_list_id']) {
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
        RelationQuestionWord::whereIn('id', $answers->pluck('id'))->update(['deleted_at' => now()]);
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

    public function createAnswerStruct(): array
    {
        $answerStruct = $this->wordsToAsk();

        if ($this->shuffle) {
            $answerStruct = $answerStruct->shuffle()->take($this->selection_count);
        }

        return $answerStruct->mapWithKeys(fn($word) => [$word->id => null])->toArray();
    }

    public function getAnswerStructFromTestTake(string $testTakeUuid): array
    {
        $testTakeId = TestTake::whereUuid($testTakeUuid)
            ->pluck('id')->first();

        $testTakeRelationQuestion = $this->testTakeRelationQuestions()
            ->where('test_take_id', $testTakeId)
            ->first();


        $answerStruct = $testTakeRelationQuestion?->json['answer_struct'];

        //if TestTakeQuestions has no answer_struct yet:
        if($answerStruct !== null) {
            return $answerStruct;
        }

        $answerStruct = $this->createAnswerStruct();

        TestTakeRelationQuestion::updateOrCreate([
            'test_take_id' => $testTakeId,
            'question_id' => $this->id,
        ], [
            'json' => [
                'answer_struct' => $answerStruct,
            ],
        ]);

        return $answerStruct;
    }

    public function isFullyAnswered(Answer $answer): bool
    {
        return collect(json_decode($answer->json, true))
                ->filter(fn($answer) => $answer === null || $answer === '')
                ->isEmpty();
    }

    public function getQuestionWordsForCms(): array
    {
        return $this->questionWords
            ->sortBy(fn($relation) => $relation->word->type->getOrder())
            ->groupBy(fn($relation) => $relation->word->word_id ?? $relation->word->id)
            ->map(fn($row) => self::buildRow($row))
            ->toArray();
    }

    public static function buildRow($row, $empty = false): array
    {
        $columns = [];
        foreach (WordType::cases() as $type) {
            $questionWord = $empty ? null : $row?->first(fn($rela) => $rela->word->type === $type);

            $columns[$type->value] = CompileWordListService::buildEmptyWordItem(
                    $questionWord?->word?->text ?? '',
                    $type,
                    $questionWord?->word_id,
                    $questionWord?->word_list_id
                ) + ['selected' => $questionWord?->selected ?? false];
        }
        return $columns;
    }
}
