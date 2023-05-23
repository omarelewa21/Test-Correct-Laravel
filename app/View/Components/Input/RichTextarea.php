<?php

namespace tcCore\View\Components\Input;

use Illuminate\View\Component;

class RichTextarea extends Component
{
    public string $initFunctionCall;

    private array $editorProperties = [
        'editorId',
        'lang',
        'allowWsc',
        'maxWords',
        'maxWordOverride',
        'questionId',
    ];

    public function __construct(
        public readonly string|int $editorId,
        public ?string             $type = 'student',
        public ?bool               $disabled = false,
        public null|string|int     $questionId = null,
        public ?string             $lang = 'nl_NL',
        public ?bool               $allowWsc = false,
        public null|string|int     $maxWords = null,
        public ?bool               $maxWordOverride = false,
    ) {
        $this->initFunctionCall = sprintf('%s(%s)', $this->getInitMethod(), json_encode($this->getEditorConfig()));
    }

    public function render()
    {
        return view('components.input.rich-textarea');
    }

    private function getInitMethod()
    {
        return match ($this->type) {
            'cms' => "RichTextEditor.initForTeacher",
            'cms-completion' => "RichTextEditor.initCompletionCMS",
            'cms-selection' => "RichTextEditor.initSelectionCMS",
            'student-co-learning' => "RichTextEditor.initStudentCoLearning",
            'student-preview' => "RichTextEditor.initClassicEditorForStudentPreviewplayer",
            'assessment-feedback' => "RichTextEditor.initAssessmentFeedback",
            default => "RichTextEditor.initClassicEditorForStudentplayer",
        };
    }

    /**
     * @return array
     */
    private function getEditorConfig(): array
    {
        $config = collect();
        foreach ($this->editorProperties as $key) {
            $config->put($key, $this->$key);
        }
        return $config->filter()->toArray();
    }
}
