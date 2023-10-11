<?php

namespace tcCore\Observers;

use tcCore\WordList;

class WordListObserver extends VersionableObserver
{
    public function deleting(WordList $wordList): bool
    {
//        if ($wordList->isUnused()) {
            return true;
//        }
    }
}
