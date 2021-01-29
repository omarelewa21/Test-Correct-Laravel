<?php


namespace tcCore\Http\Traits;


use tcCore\Attachment;

trait WithAttachments
{
    public $attachment;

    public function showAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function closeAttachmentModal()
    {
        $this->attachment = null;
    }
}