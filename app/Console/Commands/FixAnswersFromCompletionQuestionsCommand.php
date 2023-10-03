<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use tcCore\Answer;
use tcCore\AnswerRating;

class FixAnswersFromCompletionQuestionsCommand extends Command
{
    protected $signature = 'fix:answers-from-completion-questions';

    protected $description = 'Command description';

    public function handle(): void
    {
        $answerDiscrepancies = collect();

        Answer::select('answers.*')
            ->selectSub(function ($query) {
                $query->from('completion_question_answers as cqa')
                    ->leftJoin(
                        'completion_question_answer_links as cqal',
                        'cqal.completion_question_answer_id',
                        '=',
                        'cqa.id'
                    )
                    ->whereColumn('cqal.completion_question_id', 'answers.question_id')
                    ->whereNull('cqal.deleted_at')
                    ->selectRaw('MAX(tag)');
            }, 'tagAmount')
            ->whereIn('answers.question_id', function ($query) {
                $query->select('id')
                    ->from('completion_questions')
                    ->where('subtype', 'completion');
            })
            ->where('answers.created_at', '>', '2023-07-10')
            ->whereNotNull('answers.json')
            ->with([
                'question',
                'answerRatings' => function ($query) {
                    $query->where('type', AnswerRating::TYPE_TEACHER)->whereNotNull('rating');
                }
            ])
            ->chunkById(100, function (Collection $answers) use ($answerDiscrepancies) {
                foreach ($answers as $answer) {
                    $json = collect(json_decode($answer->json))
                        ->sortKeys()
                        ->reject(fn($value, $key) => $key == $answer->tagAmount)
                        ->toJson(JSON_FORCE_OBJECT);

                    $teacherRatings = $answer->answerRatings->where('type', AnswerRating::TYPE_TEACHER);
                    if ($teacherRatings->isNotEmpty()) {
                        $autoCheckedScore = $answer->question->checkAnswer($answer);
                        if ($rating = $teacherRatings->where('rating', '<', $autoCheckedScore)->first()) {
                            $answerDiscrepancies->push($answer->getKey());
                            $this->error(
                                sprintf(
                                    "Rating discrepancy for answer: %s, [teacher score: %s / auto score: %s]",
                                    $answer->getKey(),
                                    $rating->rating,
                                    $autoCheckedScore
                                )
                            );
                        }
                    }

                    Answer::whereId($answer->getKey())->update(['json' => $json]);
                }
            });

        $this->info($answerDiscrepancies->join(', '));
    }
}