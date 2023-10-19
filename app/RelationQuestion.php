<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Lib\Question\QuestionInterface;

class RelationQuestion extends Question implements QuestionInterface
{
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
        foreach ($answers as $answer) {
            $link = RelationQuestionWord::make($answer);
            $link->relation_question_id = $this->getKey();
            $link->save();
        }
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
}
