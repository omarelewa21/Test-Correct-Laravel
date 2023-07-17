<?php

namespace tcCore\View\Components\Attachment;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Stringable;
use Illuminate\View\Component;
use tcCore\Attachment;
use tcCore\GroupQuestion;
use tcCore\Question;

class StudentButtonsContainer extends Component
{
    public Collection $attachments;

    public function __construct(
        public Question       $question,
        public bool           $blockAttachments,
        public ?GroupQuestion $group = null,
    ) {
        $this->attachments = $this->group ? $this->group->attachments : $question->attachments;
        $this->attachments->map(fn($attachment) => $this->setAttachmentTitle($attachment));
    }

    public function render(): View
    {
        return view('components.attachment.student-buttons-container');
    }

    private function setAttachmentTitle(Attachment $attachment): Attachment
    {
        $attachment->displayTitle = $attachment->created_at->lt(now())
            ? __('test_take.attachment') . $this->getExtension($attachment)
            : $attachment->title;
        
        return $attachment;
    }

    private function getExtension(Attachment $attachment)
    {
        return str($attachment->file_extension)
            ->whenNotEmpty(fn(Stringable $string) => $string->prepend('.'));
    }
}
