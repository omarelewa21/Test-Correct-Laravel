<?php


namespace tcCore\Http\Traits;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use tcCore\Attachment;

trait WithAttachments
{
    public $attachment;

    protected function getListeners()
    {
        return ['audioIsPlayedOnce'];
    }

    public function showAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;
    }

    public function closeAttachmentModal()
    {
        $this->attachment = null;
    }

    public function audioIsPlayedOnce(Attachment $attachment)
    {
        $attachment->audioIsPlayedOnce();
    }
}