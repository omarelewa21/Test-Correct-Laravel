<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;
use InvalidArgumentException;
use tcCore\Http\Enums\WordType;

class WordList extends Versionable
{
    protected $fillable = [
        'name',
        'subject_id',
        'education_level_id',
        'education_level_year',
        'school_location_id',
    ];

    public function words()
    {
        return $this->belongsToMany(Word::class, 'word_list_word')
            ->withPivot('version')
            ->withTimestamps();
    }

    public function rows()
    {
        return $this->belongsToMany(Word::class, 'word_list_word')
            ->whereNull('words.word_id')
            ->with('associations')
            ->withPivot('version')
            ->withTimestamps();
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
        $word = $this->words
            ->where('id', $word->getKey())
            ->first();
        $word?->setEditingAuthor($this->getEditingAuthor());
        return $word;
    }

    public function editWord($word, $attributes): ?Word
    {
        $wordModel = $this->findWord($word);
        if (!$wordModel) {
            return null;
        }

        return $wordModel->edit($attributes);
    }

    public function removeWord(Word $word): void
    {
        $list = static::resolveVersionableInstance($this);

        $list->words()->detach($word);

        if ($word->isUnused()) {
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

    public function handleDuplication(): WordList
    {
        $newList = $this->replicateWithVersion($this->getEditingAuthor())
            ->syncRelationsFrom($this);
        $this->setUpdatedVersion($newList);

        return $newList;
    }

    public function isUsed(): bool
    {
        return $this->questions()->exists(); //$this->words()->exists();
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
        $updatedList = $this->getUpdatedListToDiffAgainst();

        $diff = collect([
            'updated' => collect(),
            'created' => collect(),
            'deleted' => collect(),
        ]);

//        $commonWords = $this->words->intersect($updatedList->words);
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

    private function getUpdatedListToDiffAgainst(): WordList
    {
        $updatedList = WordList::select('word_lists.*')
            ->join('versions', 'versions.versionable_id', '=', 'word_lists.id')
            ->where('versions.original_id', $this->getKey())
            ->whereRaw('versions.original_id != versions.versionable_id')
            ->with(['words', 'words.versions', 'rows'])
            ->first();

        $updatedList
            ->words
            ->each(function ($word) {
                $word->original_id = $word->versions()
                    ->where('original_id', '!=', $word->getKey())
                    ->first()
                    ->original_id;
            });
        return $updatedList;
    }
}
