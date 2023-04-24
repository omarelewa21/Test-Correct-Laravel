<?php

namespace tcCore\Http\Livewire\Modal;

use tcCore\Attachment;

class PreviewAttachment extends Preview
{
    public Attachment $attachment;
    public string $attachmentType;
    public ?string $iconComponentName = null;
    public string $questionUuid;

    public string $source;

    public function mount(string $attachmentUuid, string $questionUuid)
    {
        $this->attachment = Attachment::whereUuid($attachmentUuid)->first();
        $this->questionUuid = $questionUuid;

        $this->setProperties();
    }

    public function render()
    {
        if (in_array($this->attachmentType, ['video', 'audio', 'image', 'pdf'])) {
            return view("livewire.modal.preview-attachment-{$this->attachmentType}");
        }
        return view("livewire.modal.preview-attachment");
    }

    protected function setProperties()
    {
        $this->attachmentType = $this->attachment->getFileType();
        $iconNameSuffix = $this->attachmentType;

        $this->setTitle();
        $this->setSource();

        if ($this->attachmentType === 'video') {
            $iconNameSuffix = Attachment::getVideoHost($this->attachment->link);
        }

        $this->iconComponentName = sprintf('icon.%s', $iconNameSuffix);
    }

    protected function setTitle(): void
    {
        $this->title = $this->attachment->title;
    }

    private function setSource(): void
    {
        $prefix = auth()->user()->isA('Student') ? 'student' : 'teacher.preview';
        $route = $this->attachmentType === 'pdf' ? 'question-pdf-attachment-show' : 'question-attachment-show';
        $this->source = route(
            $prefix . '.' . $route,
            [
                'attachment' => $this->attachment->uuid,
                'question'   => $this->questionUuid
            ]
        );
    }
}
