<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;
use tcCore\Http\Enums\WscLanguage;

class RichTextarea extends Component
{
    public string $initFunctionCall;



    protected array $editorProperties = [
        'editorId',
        'lang',
        'allowWsc',
        'questionId',
        'maxWords',
        'maxWordOverride',
        'restrictWords',
        'textFormatting',
        'mathmlFunctions',
        'enableGrammar',
    ];

    public function __construct(
        public readonly string|int     $editorId,
        public ?string                 $type = null,
        public ?bool                   $disabled = false,
        public null|string|int         $questionId = null,
        public null|string|WscLanguage $lang = 'nl_NL',
        public ?bool                   $allowWsc = false,
        public null|string|int         $maxWords = null,
        public ?bool                   $maxWordOverride = false,
        public ?bool                   $restrictWords = false,
        public ?bool                   $textFormatting = true,
        public ?bool                   $mathmlFunctions = true,
        public ?bool                   $enableGrammar = true,
    ) {
        $this->lang ??= WscLanguage::DUTCH;
        $this->initFunctionCall = sprintf('%s(%s)', $this->getInitMethod(), json_encode($this->getEditorConfig()));
    }

    public function render()
    {
        return view('components.input.rich-textarea');
    }

    protected function getInitMethod()
    {
        return match ($this->type) {
            'cms' => "RichTextEditor.initForTeacher",
            'cms-completion' => "RichTextEditor.initCompletionCMS",
            'cms-selection' => "RichTextEditor.initSelectionCMS",
            'student-co-learning' => "RichTextEditor.initStudentCoLearning",
            'student-preview' => "RichTextEditor.initClassicEditorForStudentPreviewplayer",
            'assessment-feedback' => "RichTextEditor.initAssessmentFeedback",
            'create-answer-feedback' => "RichTextEditor.initCreateAnswerFeedbackEditor",
            'update-answer-feedback' => "RichTextEditor.initUpdateAnswerFeedbackEditor",
            default => "RichTextEditor.initClassicEditorForStudentPlayer",
        };
    }

    /**
     * @return array
     */
    protected function getEditorConfig(): array
    {
        $config = collect();
        foreach ($this->editorProperties as $key) {
            $config->put($key, $this->$key);
        }
        return $config->toArray();
    }
}
