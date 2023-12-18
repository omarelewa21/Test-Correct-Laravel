<?php

namespace tcCore\View\Components\Answer\Teacher;

use Illuminate\Support\Collection;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Word;

class RelationQuestion extends QuestionComponent
{
    public $answerStruct; // id => (string) correct answer
    public $questionStruct; // id => Word

    /**
     * Create AnswerModel answer struct:
     *  The source of the RelationQuestion answer struct depends on whether the question is shuffled/a carousel
     *  and whether the user is a student or a teacher.
     *
     *  The teacher sees: all words that are selected or the answer struct of the TestTake
     */
    protected function setAnswerStruct($question): void
    {
        $answerModelWordIds = $this->getWordsToShowInAnswerModel($question);

        $answerModelWords = Word::whereIn('id', $answerModelWordIds)->get()->keyBy('id');

        $this->answerStruct = $answerModelWordIds->mapWithKeys(function($wordId) use ($answerModelWords) {
            $questionPrefixTranslation = $answerModelWords[$wordId]?->type->value !== 'subject'
                ? __('question.word_type_'.$answerModelWords[$wordId]?->type->value)
                : null;

            return [
                $wordId => [
                    'answer'   => $answerModelWords[$wordId]->correctAnswerWord()->text,
                    'question' => $answerModelWords[$wordId]->text,
                    'question_prefix' =>  $questionPrefixTranslation,
                ]
            ];
        });
    }


    /**
     * returns an Collection with the ids of the words that should be shown in the answer model of this screen as keys
     *  depends on whether the user is a student or a teacher.
     *  depends on whether the question is one fixed carousel for the entire TestTake, or a different one for each student
     */
    protected function getWordsToShowInAnswerModel($question): Collection
    {
        $userIsAStudent = $this->answer && $this->answer->exists;

        //Caroussel the same for all students
        if($question->shuffleCarouselPerTestTake()) {
            return collect($this->testTake->testTakeRelationQuestions()->where('question_id', $question->id)->first()->json)->keys();
        }

        //Caroussel per student and created for a student screen
        if ($userIsAStudent && $question->shuffleCarouselPerTestParticipant()) {
            return collect(json_decode($this->answer->json, true))->keys();
        }

        //No caroussel, or answer model for a teacher screen
        return $question->wordsToAsk()->map->id;
    }
}