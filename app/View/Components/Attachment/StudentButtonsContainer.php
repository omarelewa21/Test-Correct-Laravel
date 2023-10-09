<?php

namespace tcCore\View\Components\Attachment;

use Carbon\Carbon;
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
    public Collection $questionAttachments;
    public Carbon $transitionDate;

    public function __construct(
        public Question       $question,
        public bool           $blockAttachments,
        public ?GroupQuestion $group = null,
    ) {
        /* Mon Aug 07 2023 00:00:00 GMT+0200 (Central European Summer Time) */
        $this->transitionDate = Carbon::parse(1691359200);
        $this->attachments = collect($this->getGroupAttachments());
        $this->attachments->map(fn($attachment) => $this->setAttachmentTitle($attachment));
        $this->questionAttachments = collect($this->question->attachments);
        $this->questionAttachments->map(fn($questionAttachments) => $this->setAttachmentTitle($questionAttachments));
    }

    public function render(): View
    {
        return view('components.attachment.student-buttons-container');
    }

    private function setAttachmentTitle(Attachment $attachment): Attachment
    {
        $attachment->displayTitle = $attachment->created_at->lt($this->transitionDate)
            ? __('test_take.attachment') . $this->getExtension($attachment)
            : $attachment->title;
        
        return $attachment;
    }

    private function getExtension(Attachment $attachment)
    {
        return str($attachment->file_extension)
            ->whenNotEmpty(fn(Stringable $string) => $string->prepend('.'));
    }

    private function getGroupAttachments(): Collection
    {
        if (!$this->group) {
            return collect();
        }
        $this->group->attachments->each(function ($attachment) {
            if ($attachment === $this->group->attachments->last()) {
                $attachment->groupDivider = true;
            }
        });

        return $this->group->attachments ?? collect();
    }

}
