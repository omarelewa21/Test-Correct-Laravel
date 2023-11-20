<?php

namespace tcCore;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;
use tcCore\Observers\VersionableObserver;

class Word extends Versionable
{
    protected const TEXT_FILTERS = ['text'];

    protected $fillable = [
        'text',
        'type',
        'word_id',
        'subject_id',
        'education_level_id',
        'education_level_year',
        'school_location_id',
    ];

    /* Attribute to merge with parent $casts property */
    private array $wordCasts = [
        'type' => WordType::class,
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->mergeCasts($this->wordCasts);
    }

    public function wordLists(): BelongsToMany
    {
        return $this->belongsToMany(WordList::class, 'word_list_word')
            ->withPivot('version');
    }

    public function associations(): HasMany
    {
        return $this->hasMany(Word::class, 'word_id');
    }

    public function subjectWord(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(
            RelationQuestion::class,
            RelationQuestionWord::class,
            'word_id',
            'relation_question_id',
        );
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public static function build(
        string   $text,
        WordType $type,
        User     $author,
        int      $subjectId,
        int      $educationLevelId,
        int      $educationLevelYear,
        int      $schoolLocationId,
        ?int     $parentId = null,
    ) {
        /* TODO: Force parentId if WordType != Subject? */
        if ($type !== WordType::SUBJECT && !$parentId) {
            /* Do we allow this? */
        }

        $newWord = self::make([
            'text'                 => $text,
            'type'                 => $type,
            'subject_id'           => $subjectId,
            'education_level_id'   => $educationLevelId,
            'education_level_year' => $educationLevelYear,
            'school_location_id'   => $schoolLocationId,
            'word_id'              => $parentId
        ])->associateAuthor($author);
        $newWord->setEditingAuthor($author);
        $newWord->save();
        $newWord->associateVersion();

        return $newWord;
    }

    public function syncRelationsFrom(Versionable $original): static
    {
        $this->wordLists()->sync($original->wordLists->pluck('id'));
        return $this;
    }

    public function handleDuplication()
    {
        $list = $this->getPivotListToSyncDuplicateWith();
        $newWord = $this->replicateWithVersion($this->getEditingAuthor());
        $this->setUpdatedVersion($newWord);

        $list->words()->detach($this);
        $list->words()->attach($newWord);

        return $newWord;
    }

    private function getPivotListToSyncDuplicateWith(): WordList
    {
        $wordList = WordList::where('id', $this->pivot->word_list_id)->first();
        $wordList->setEditingAuthor($this->getEditingAuthor());

        return static::resolveVersionableInstance($wordList);
    }

    public function isUsed(): bool
    {
        if (VersionableObserver::isMassUpdating($this->pivot?->word_list_id, WordList::class)) {
            return false;
        }
        if ($this->wordLists()->where('word_lists.id', '!=', $this->pivot?->word_list_id)->exists()) {
            return true;
        }

        return $this->questions()->exists();
    }

    public function isUnused(): bool
    {
        return !$this->isUsed();
    }

    public function isSubjectWord(): bool
    {
        return $this->type === WordType::SUBJECT;
    }
}
