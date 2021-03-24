<?php


namespace tcCore\Http\Traits;


use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use tcCore\Answer;
use tcCore\Attachment;
use tcCore\Http\Requests\Request;

trait WithAttachments
{
    public $attachment;
    public $audioCloseWarning = false;
    public $pressedPlay = false;
    public $timeout;
    public $answerId;

    public function mountWithAttachments()
    {
//        $this->answerId = Answer::whereId($this->answers[$this->question->uuid]['id'])->first()->uuid;
    }

    public function showAttachment(Attachment $attachment)
    {
        $this->attachment = $attachment;
        $this->timeout = $this->attachment->audioTimeoutTime();
    }

    public function closeAttachmentModal()
    {
        if (optional($this->attachment)->file_mime_type == 'audio/mpeg') {
            if ($this->audioIsPlayedAndCanBePlayedAgain() && !$this->audioCloseWarning) {
                if (!$this->attachment->audioIsPausable()) {
                    $this->audioCloseWarning = true;
                    return;
                } else {
                    $this->dispatchBrowserEvent('pause-audio-player');
                }
            }

            if ($this->audioCloseWarning) {
                $this->attachment->audioIsPlayedOnce();
                $this->audioCloseWarning = false;
            }
            if ($this->timeout != null) {
                $data = ['timeout' => $this->timeout, 'attachment' => $this->attachment->getKey()];
                $this->dispatchBrowserEvent('start-timeout', $data);
            }
        }


        $this->attachment = null;
    }

    public function audioIsPlayedOnce(Attachment $attachment)
    {
        $attachment->audioIsPlayedOnce();
    }

    public function audioStoreCurrentTime(Attachment $attachment, $currentTime)
    {
        $sessionValue = 'attachment_' . $attachment->getKey() . '_currentTime';
        session()->put($sessionValue, $currentTime);
    }

    public function updating(&$name, &$value)
    {
        Request::filter($value);
    }

    private function audioIsPlayedAndCanBePlayedAgain()
    {
        return $this->attachment->audioOnlyPlayOnce()
            && $this->attachment->audioCanBePlayedAgain()
            && ($this->attachment->audioHasCurrentTime()
                || $this->pressedPlay);
    }
}