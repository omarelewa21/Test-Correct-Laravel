<?php

namespace tcCore;


use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use tcCore\Http\Enums\WordType;

class Word extends Versionable
{
    protected $fillable = [
        'text',
        'type',
        'word_id',
        'subject_id',
        'education_level_id',
        'education_level_year',
        'school_location_id',
    ];

    protected $casts = [
        'type' => WordType::class,
    ];

    public function wordLists(): BelongsToMany
    {
        return $this->belongsToMany(WordList::class, 'word_list_word');
    }

    public function associates(): HasMany
    {
        return $this->hasMany(Word::class, 'word_id');
    }

    public function subjectWord(): BelongsTo
    {
        return $this->belongsTo(Word::class, 'word_id');
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

    public function edit(array $properties): Word
    {
        $this->fill($properties);
        $this->save();
        return $this;
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
        return $this->wordLists()->exists();
    }

    public function isUnused(): bool
    {
        return !$this->isUsed();
    }
}
