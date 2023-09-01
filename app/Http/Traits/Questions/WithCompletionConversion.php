<?php

namespace tcCore\Http\Traits\Questions;

use Illuminate\Support\Collection;

trait WithCompletionConversion
{
    private string $searchPattern = '/\[([0-9]+)\]/i'; /* Can't use trait consts until PHP8.2 */

    private function explodeQuestionTextPartialIntoWordsAndHtmlTags($partial): Collection
    {
        preg_match_all('/<[^>]++>|[^<>\s]++/', $partial, $stringPartialsArray);
        return collect($stringPartialsArray)->flatten();
    }

    private function concatenateWirisMathTagsInQuestionPartialsArray(Collection $stringPartialsArray): void
    {
        $stringPartialsArray->filter(function ($item) {
            return (str_contains($item, '<math') || str_contains($item, '</math'));
        })
            ->mapWithKeys(function ($tag, $index) {
                return [$index => ['tag' => $tag, 'index' => $index]];
            })
            ->chunk(2)
            ->each(function ($item) use ($stringPartialsArray) {
                $startIndex = $item->first()['index'];
                $endIndex = $item->last()['index'];
                $concatenatedMathTagString = '';
                for ($i = $startIndex; $i <= $endIndex; $i++) {
                    $concatenatedMathTagString .= $stringPartialsArray->pull($i);
                }
                $stringPartialsArray[$startIndex] = $concatenatedMathTagString;
            });
    }

    private function addBreaksAndSpanTagsToQuestionPartials(Collection &$stringPartialsArray): void
    {
        $stringPartialsArray = $stringPartialsArray->map(function ($word) {
            if (in_array($word, ['</p>', '</table>', '</ol>', '</ul>'])) {
                return sprintf('%s<span class="co-learning-break"></span>', $word);
            }
            if (str_contains($word, chr(60))) {
                return $word;
            }
            if (in_array($word, ['.', ',', ':', ';', '?', '!'])) {
                return sprintf('<span class="mr-1 -ml-2">%s</span>', $word);
            }
            return sprintf('<span class="mr-1">%s</span>', $word);
        });
    }

    protected function explodeAndModifyQuestionText($question_text): Collection
    {
        return collect(explode('(##)', preg_replace($this->searchPattern, '(##)', $question_text)))
            ->map(function ($partial) {
                $stringPartialsArray = $this->explodeQuestionTextPartialIntoWordsAndHtmlTags($partial);

                $this->concatenateWirisMathTagsInQuestionPartialsArray($stringPartialsArray);

                $this->addBreaksAndSpanTagsToQuestionPartials($stringPartialsArray);

                return $stringPartialsArray;
            });
    }

    protected function countCompletionQuestionOptions($question_text): int
    {
        return preg_match_all($this->searchPattern, $question_text);
    }
}