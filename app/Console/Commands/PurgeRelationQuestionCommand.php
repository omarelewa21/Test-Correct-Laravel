<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\GroupQuestionQuestion;
use tcCore\Question;
use tcCore\RelationQuestion;
use tcCore\RelationQuestionWord;
use tcCore\TestQuestion;
use tcCore\TestTakeQuestion;
use tcCore\TestTakeRelationQuestion;
use tcCore\Word;
use tcCore\WordList;
use tcCore\WordListWord;

class PurgeRelationQuestionCommand extends Command
{
    protected $signature = 'purge:relation-question';

    protected $description = 'Purge the database of any and all relation question related data.';

    public function handle(): bool
    {
        if (!in_array(config('app.env'), ['local', 'testing'])) {
            $this->error('You cannot perform this action on this environment! only with APP_ENV set to local!!');
            return false;
        }

        $relationQuestionIds = RelationQuestion::pluck('id');

        try {
            DB::transaction(function () use ($relationQuestionIds) {
                Answer::whereIn('question_id', $relationQuestionIds)->delete();
                TestQuestion::whereIn('question_id', $relationQuestionIds)->delete();
                TestTakeQuestion::whereIn('question_id', $relationQuestionIds)->delete();
                GroupQuestionQuestion::whereIn('question_id', $relationQuestionIds)->delete();

                DB::delete('DELETE FROM relation_question_word');
                DB::delete('DELETE FROM test_take_relation_questions');
                DB::delete('DELETE FROM word_list_word');
                DB::delete('DELETE FROM word_lists');
                DB::delete('DELETE FROM words');
                Question::where('type', 'RelationQuestion')->delete();
                DB::delete('DELETE FROM relation_questions');
            });
        } catch (\Exception $exception) {
            logger($exception);
            $this->error('Could not purge relation questions. Check the logs.');
        }

        $this->info("RelationQuestions successfully purged");
        return true;
    }
}
