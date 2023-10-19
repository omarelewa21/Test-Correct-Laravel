<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordListWord extends Model
{
    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }
}
