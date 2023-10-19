<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use tcCore\Lib\Models\BaseModel;

class RelationQuestionWord extends BaseModel
{
    protected $fillable = [
        'uuid',
        'relation_question_id',
        'word_id',
        'word_list_id',
        'selected'
    ];

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'selected' => 'boolean'
    ];

    protected $table = 'relation_question_word';

    public function question(): BelongsTo
    {
        return $this->belongsTo(RelationQuestion::class);
    }

    public function wordList(): BelongsTo
    {
        return $this->belongsTo(WordList::class);
    }

    public function word(): BelongsTo
    {
        return $this->belongsTo(Word::class);
    }
}
