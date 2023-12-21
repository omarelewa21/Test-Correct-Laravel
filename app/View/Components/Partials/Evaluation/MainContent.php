<?php

namespace tcCore\View\Components\Partials\Evaluation;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\Component;
use tcCore\Question;
use tcCore\TestTake;
use tcCore\View\Components\CompletionQuestionConvertedHtml;

class MainContent extends Component
{
    public $questionText;

    public function __construct(
        public Question   $question,
        public string     $uniqueKey,
        public string|int $navigationValue,
        public bool       $groupPanel,
        public bool       $questionPanel,
        public bool       $answerModelPanel,
        public bool       $showCorrectionModel,
        public TestTake   $testTake,
        public ?Question  $group = null,
    ) {
        $this->questionText = $this->getQuestionText($question);
    }

    public function render(): View
    {
        return view('components.partials.evaluation.main-content');
    }

    private function getQuestionText(Question $question)
    {
        if ($question->isType('Completion')) {
            return Blade::renderComponent(new CompletionQuestionConvertedHtml($question, 'assessment'));
        }
        return $question->converted_question_html;
    }
}
