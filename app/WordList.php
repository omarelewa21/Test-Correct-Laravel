<?php

namespace tcCore;

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
            ->with('types')
            ->withPivot('version')
            ->withTimestamps();
    }

    /* Word CRUD actions */
    /**
     * Creates a new Word instance and adds it to the list
     * @param string $text
     * @param WordType $type
     * @return void
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
     * @return void
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

        $wordModel->edit($attributes);
        return $wordModel;
    }

    public function removeWord(Word $word): void
    {
        if ($this->user->isNot($this->getEditingAuthor())) {
            throw new UnauthorizedException('Cannot delete words from other peoples list!');
        }

        $this->words()->detach($word);

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

        return $subjectWord->load('associates');
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

    public function edit(array $attributes): static
    {
        $this->fill($attributes);
        $this->save();
        return $this;
    }

    public function remove(): void
    {
        $this->delete();
    }

    public function handleDuplication(): WordList
    {
        return $this->replicateWithVersion($this->getEditingAuthor())->syncRelationsFrom($this);
    }

    public function isUsed(): bool
    {
        return $this->words()->exists();
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
            throw new InvalidArgumentException('Cannot create a row 2 words of the same type.');
        }
    }

    public function addRow(Word $subjectWord)
    {
        if ($subjectWord->type !== WordType::SUBJECT) {
            throw new InvalidArgumentException('To add an existing row, insert the subject word.');
        }

        $this->addWord($subjectWord);
        $subjectWord->associates->each(fn($word) => $this->addWord($word));

        return $subjectWord;
    }
}
