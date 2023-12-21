<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use tcCore\Http\Enums\WordType;
use tcCore\Observers\VersionableObserver;

class WordList extends Versionable
{
    protected const TEXT_FILTERS = ['name'];
    protected $fillable = [
        'name',
        'subject_id',
        'education_level_id',
        'education_level_year',
        'school_location_id',
        'hidden',
    ];

    /* Attribute to merge with parent $casts property */
    private array $listCasts = [
        'hidden' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->mergeCasts($this->listCasts);
    }

    public function words()
    {
        return $this->belongsToMany(Word::class, 'word_list_word')
            ->withPivot('version')
            ->withTimestamps();
    }

    public function rows(bool $fresh = false): Collection
    {
        if ($fresh) {
//            $this->words = $this->words()->get();
//            $this->relationLoaded('words') ? $this->words->refresh() : $this->load('words');
            /* TODO: Figure out way to correctly reload property. Is this double? */
            unset($this->words);
            $this->words->fresh();
        }

        return $this
            ->words
            ->groupBy(fn($word) => $word->word_id ?? $word->id)
            ->map(fn($group) => $group->sortBy(fn($word) => $word->type->getOrder()))
            ->values();
    }

    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(
            RelationQuestion::class,
            RelationQuestionWord::class,
            'word_list_id',
            'relation_question_id',
        )->distinct();
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /* Word CRUD actions */
    /**
     * Creates a new Word instance and adds it to the list
     * @param string $text
     * @param WordType $type
     * @param Word|null $subjectWord
     * @return Word
     */
    public function createWord(string $text, WordType $type, ?Word $subjectWord = null): Word
    {
        $newWord = Word::build(
            $text,
            $type,
            $this->getEditingAuthor(),
            $this->subject_id,
            $this->education_level_id,
            $this->education_level_year,
            $this->school_location_id,
            $subjectWord?->id
        );
        return $this->addWord($newWord);
    }

    /**
     * Add existing Word to the WordList
     * @param Word $word
     * @return Word
     */
    public function addWord(Word $word): Word
    {
        // if I am adding a word to someone else's list,
        // I need to duplicate the list and only add the word to my new version
        static::resolveVersionableInstance($this)
            ->words()
            ->attach($word);
        return $word;
    }

    private function findWord(Word $word): ?Word
    {
        $word = $this->words()
            ->where('words.id', $word->getKey())
            ->first();
        $word?->setEditingAuthor($this->getEditingAuthor());
        return $word;
    }

    public function editWord($word, $attributes): ?Word
    {
        $wordModel = $this->findWord($word);
        return $wordModel?->edit($attributes);
    }

    public function removeWord(Word $word): void
    {
        $list = static::resolveVersionableInstance($this);

        $list->words()->detach($word);

        if ($word->wordLists()->doesntExist() && $word->isUnused()) {
            $word->delete();
        }
    }

    /* End Word CRUD actions */

    public function createRow($word1, $word2, ...$words): Word
    {
        /*Force at least 2 words for any row to be valid */
        $words = collect([$word1, $word2])->concat($words);
        $this->validateRowProposal($words);

        $subjectProposal = $this->getRowSubjectProposal($words);

        $words = $words->reject(fn($word) => $word === $subjectProposal);

        /* Words with the Subject language type are always leading in the row.
         * Everything else is a subtype of that word
         */
        $subjectWord = $this->createWord(...$subjectProposal);
        $words->each(fn($word) => $this->createWord($word['text'], $word['type'], $subjectWord));

        return $subjectWord->load('associations');
    }

    public function syncRelationsFrom(Versionable $original)
    {
        $this->words()->sync($original->words->pluck('id'));

        return $this;
    }

    public static function build(
        string $name,
        User   $author,
        int    $subjectId,
        int    $educationLevelId,
        int    $educationLevelYear,
        int    $schoolLocationId,
    ): WordList {
        $wordList = self::make([
            'name'                 => $name,
            'subject_id'           => $subjectId,
            'education_level_id'   => $educationLevelId,
            'education_level_year' => $educationLevelYear,
            'school_location_id'   => $schoolLocationId,
        ])->associateAuthor($author);

        $wordList->save();

        $wordList->associateVersion();

        return $wordList;
    }

    public function remove(): void
    {
        $this->delete();
    }

    public function handleDuplication(bool $newOriginal = false): WordList
    {
        $newList = $this->replicateWithVersion($this->getEditingAuthor(), $newOriginal)->syncRelationsFrom($this);
        $this->setUpdatedVersion($newList);

        return $newList;
    }

    public function isUsed($exclusions = null): bool
    {
        if (VersionableObserver::isMassUpdating($this->getKey(), self::class)) {
            return false;
        }

        return $this
            ->questions()
            ->when(
                $exclusions,
                fn($builder) => $builder->whereNot('relation_question_id', $exclusions->getKey()),
            )
            ->exists();
    }

    public function isUnused(): bool
    {
        return !$this->isUsed();
    }

    private function getRowSubjectProposal(Collection $words): array
    {
        $subjectProposal = $words->where(fn($word) => $word['type'] === WordType::SUBJECT)->first();
        if (!$subjectProposal) {
            throw new InvalidArgumentException('Cannot create a row without a Subject language word.');
        }
        return $subjectProposal;
    }

    private function validateRowProposal(Collection $words): void
    {
        if ($words->duplicates('type')->isNotEmpty()) {
            throw new InvalidArgumentException('Cannot create a row with 2 words of the same type.');
        }
    }

    public function addRow(Word $subjectWord): Word
    {
        if (!$subjectWord->isSubjectWord()) {
            /* TODO: Should I figure out the subject word from this one, and add that instead? */
            throw new InvalidArgumentException('To add an existing row, insert the subject word.');
        }

        $list = static::resolveVersionableInstance($this);
        $list->load('words');

        $subjectWord = $list->addWord($subjectWord);
        $subjectWord->associations->each(function ($word) use ($list) {
            if ($list->words->contains($word)) {
                return true;
            }
            return $list->addWord($word);
        });

        return $subjectWord;
    }

    public function hasNewVersion(): bool
    {
        return $this->versions()
            ->get()
            ->where('versionable_id', '>', $this->getKey())
            ->isNotEmpty();
    }

    public function getDiff(): Collection
    {
        $this->loadMissing('words');
        $updatedList = $this->getLatestVersionOfList();

        if (!$updatedList) {
            return collect();
        }

        $diff = collect([
            'list'    => $updatedList,
            'updated' => collect(),
            'created' => collect(),
            'deleted' => collect(),
        ]);

        $updatedList->words
            ->diff($this->words)
            ->each(function ($word) use ($diff) {
                $isNewWord = $this->words->where('id', $word->original_id)->isEmpty();
                $category = $isNewWord ? 'created' : 'updated';
                $diff->get($category)->push($word);
            });

        $diff['deleted'] = $this->words
            ->diff($updatedList->words)
            ->whereNotIn(
                'id',
                $diff->get('updated')->pluck('original_id')
            );

        return $diff;
    }

    public function hide(): void
    {
        $this->hidden = true;
        $this->save();
    }

    public function getLatestVersionOfList(): ?WordList
    {
        /* TODO: Is it correct that the lookup is only 1 version deep? */
        return WordList::select('word_lists.*')
            ->join('versions', 'versions.versionable_id', '=', 'word_lists.id')
            ->where('versions.original_id', $this->getKey())
            ->whereNull('versions.deleted_at')
            ->whereRaw('versions.original_id != versions.versionable_id')
            ->with(['words', 'words.versions'])
            ->first()
            ?->setOriginalIdOnWords();
    }

    private function setOriginalIdOnWords(): static
    {
        $this->words
            ->each(function ($word) {
                $word->original_id = $word->versions()
                    ->where('original_id', '!=', $word->getKey())
                    ->first()
                    ->original_id;
            });

        return $this;
    }

    public function scopeFiltered($query, array|Collection $filters = [], array|Collection $sorting = [])
    {
        return parent::scopeFiltered($query, $filters, $sorting)
            ->where(fn($query) => $query->whereNull('word_lists.hidden')->orWhere('word_lists.hidden', false));
    }
}
