<?php

namespace tcCore\Http\Livewire\CoLearning;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Livewire\Student\CoLearning;

class CompletionQuestion extends CoLearningQuestion
{
    const SEARCH_PATTERN = "/\[([0-9]+)\]/i";

    public array $answerOptions;
    public int $answerOptionsAmount;

    public $questionTextPartials;
    public $questionTextPartialFinal;


    public function render()
    {
        return view('livewire.co-learning.completion-question');
    }

    public function updatedAnswerOptions()
    {
        $this->emit('UpdateAnswerRating', $this->answerOptionsChecked, 3);
//        $this->emit('UpdateAnswerRating', $this->answerOptionsChecked, $this->answerOptionsAmount);
    }

    public function isQuestionFullyAnswered(): bool
    {
        return collect($this->answer)->count() === $this->answerOptionsAmount;
    }

    protected function handleGetAnswerData(): void
    {
        $this->answer = (array)json_decode($this->answerRating->answer->json);

        $question_text = $this->answerRating->answer->question->converted_question_html;

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question_text);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();

        $this->createAnswerOptionsDataStructure();
    }

    public function getAnswerOptionsCheckedProperty()
    {
        return collect($this->answerOptions[$this->answerRatingId])->reduce(function ($carry, $answerOption) {
            $carry += $answerOption['rating'] === '1' ? 1 : 0;
            return $carry;
        }, 0);
    }

    private function createAnswerOptionsDataStructure(): void
    {
        $this->answerOptionsAmount = $this->questionTextPartials->count();

        for ($i = 0; $i < $this->answerOptionsAmount; $i++) {
            $this->answerOptions[$this->answerRatingId][] = [
                'rating'   => null,
                'answered' => isset($this->answer[$i]),
                'answer'   => $this->answer[$i] ?? '......',
            ];
        }
    }

    private function explodeQuestionTextPartialIntoWordsAndHtmlTags($partial): \Illuminate\Support\Collection
    {
        preg_match_all('/<[^>]++>|[^<>\s]++/', $partial, $stringPartialsArray);
        return collect($stringPartialsArray)->flatten();
    }

    private function concatinateWirisMathTagsInQuestionPartialsArray(\Illuminate\Support\Collection &$stringPartialsArray): void
    {
        $stringPartialsArray->filter(function ($item) {
            return (strpos($item, '<math') !== false || strpos($item, '</math') !== false);
        })->mapWithKeys(function ($tag, $index) {
            return [$index => ['tag' => $tag, 'index' => $index]];
        })->chunk(2)
            ->each(function ($item) use ($stringPartialsArray) {
                $startIndex = $item->first()['index'];
                $endIndex = $item->last()['index'];
                $concatinatedMathTagString = '';
                for ($i = $startIndex; $i <= $endIndex; $i++) {
                    $concatinatedMathTagString .= $stringPartialsArray->pull($i);
                }
                $stringPartialsArray[$startIndex] = $concatinatedMathTagString;
            });
    }

    private function addBreaksAndSpanTagsToQuestionPartials(\Illuminate\Support\Collection &$stringPartialsArray): void
    {
        $stringPartialsArray = $stringPartialsArray->map(function ($word) {
            if (in_array($word, ['</p>', '</table>', '</ol>', '</ul>'])) {
                return sprintf('%s<span class="co-learning-break"></span>', $word);
            }
            if (strpos($word, chr(60)) !== false) {
                return $word;
            }
            if (in_array($word, ['.', ',', ':', ';', '?', '!'])) {
                return sprintf('<span class="mr-1 -ml-1">%s</span>', $word);
            }
            return sprintf('<span class="mr-1">%s</span>', $word);
        });
    }

    private function explodeAndModifyQuestionText($question_text): \Illuminate\Support\Collection
    {
        return collect(explode('(##)', preg_replace(self::SEARCH_PATTERN, '(##)', $question_text)))
            ->map(function ($partial) {
                $stringPartialsArray = $this->explodeQuestionTextPartialIntoWordsAndHtmlTags($partial);

                $this->concatinateWirisMathTagsInQuestionPartialsArray($stringPartialsArray);

                $this->addBreaksAndSpanTagsToQuestionPartials($stringPartialsArray);

                return $stringPartialsArray;
            });
    }
}
