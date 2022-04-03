<?php


namespace tcCore\Http\Traits;


use tcCore\Answer;
use tcCore\Attachment;

trait WithAttachments
{
    public $attachment;
    public $audioCloseWarning = false;
    public $pressedPlays = [];
    public $timeout;
    public $answerId;
    public $attachmentType = '';
    public $positionTop;
    public $positionLeft;
    public $blockAttachments = false;


    public $currentTimes = [];
    public $playedOnce = [];
    public $playedTotalAudio = [];

    public function mountWithAttachments()
    {
        $this->answerId = $this->answers[$this->question->uuid]['uuid'];
        $this->question->loadMissing('attachments');
    }

    public function showAttachment($attachment)
    {
        if($this->audioCloseWarning){
            return;
        }
        $this->attachment = Attachment::whereUuid($attachment)->first();
        $this->timeout = $this->attachment->audioTimeoutTime();
        $this->attachmentType = $this->getAttachmentType($this->attachment);
    }

    public function closeAttachmentModal()
    {
        if (optional($this->attachment)->file_mime_type == 'audio/mpeg') {
            if ($this->audioHasTimerAndIsStartedAndNotFinished()&& !$this->audioCloseWarning){
                if ($this->attachment->audioIsPausable()) {
                    $this->dispatchBrowserEvent('pause-audio-player');
                }
                $this->audioCloseWarning = true;
                return;
            }

            if ($this->audioOnlyPlayOnceAndIsStartedAndNotFinished() && !$this->audioCloseWarning) {
                if (!$this->attachment->audioIsPausable()) {
                    $this->audioCloseWarning = true;
                    return;
                }
            }

            if ($this->audioCloseWarning&&$this->attachment->audioOnlyPlayOnce()) {
                $this->audioIsPlayedOnce();
            }

            $this->audioCloseWarning = false;
            $this->dispatchBrowserEvent('pause-audio-player');

            if ($this->timeout != null && $this->playStarted()) {
                $data = ['timeout' => $this->timeout, 'attachment' => $this->attachment->getKey()];
                $this->dispatchBrowserEvent('start-timeout', $data);
                $this->unsetPlayedTotalAudio();
            }
        }


        $this->attachment = null;
    }

    public function audioIsPlayedOnce()
    {
        $this->playedOnce[] = $this->attachment->uuid;
        $this->attachment->audioIsPlayedOnce();
    }

    public function audioStoreCurrentTime($attachmentUuid, $currentTime)
    {
        $sessionValue = 'attachment_' . $attachmentUuid . '_currentTime';
        session()->put($sessionValue, $currentTime);
        $this->currentTimes[$this->question->uuid][$attachmentUuid] = $currentTime;
    }

    public function registerPlayStart()
    {
        $this->pressedPlays[$this->question->uuid][$this->attachment->uuid] = true;
    }

    public function registerEndOfAudio($length,$currentTime)
    {
        if($length==$currentTime){
            $this->playedTotalAudio[] = $this->attachment->uuid;
        }

    }

    public function unsetPlayedTotalAudio()
    {
        if (($key = array_search($this->attachment->uuid, $this->playedTotalAudio)) !== false) {
            unset($this->playedTotalAudio[$key]);
        }
    }

    public function totalAudioPlayed()
    {
        if(in_array($this->attachment->uuid,$this->playedTotalAudio)){
            return true;
        }
        return false;
    }

    public function playStarted()
    {
        if(array_key_exists($this->question->uuid,$this->pressedPlays)&&array_key_exists($this->attachment->uuid,$this->pressedPlays[$this->question->uuid])){
            return true;
        }
        return false;
    }

    public function getCurrentTime()
    {
        if(array_key_exists($this->question->uuid,$this->currentTimes)&&array_key_exists($this->attachment->uuid,$this->currentTimes[$this->question->uuid])){
            return $this->currentTimes[$this->question->uuid][$this->attachment->uuid];
        }
        return 0;
    }

    private function audioOnlyPlayOnceAndIsStartedAndNotFinished()
    {
        return $this->attachment->audioOnlyPlayOnce()
            && $this->audioCanBePlayedAgain()
            && ($this->attachment->audioHasCurrentTime()
                || $this->playStarted());
    }

    private function audioHasTimerAndIsStartedAndNotFinished()
    {
        return $this->attachment->hasAudioTimeout()
                && !$this->totalAudioPlayed()
                && ($this->attachment->audioHasCurrentTime()
                || $this->playStarted());
    }

    private function audioCanBePlayedAgain()
    {
        if(!$this->attachment->audioCanBePlayedAgain()){
            return false;
        }
        if(in_array($this->attachment->uuid, $this->playedOnce)){
            return false;
        }
        return true;
    }

    private function getAttachmentType($attachment)
    {
        if ($attachment->type == 'video') return 'video';
        if ($attachment->file_mime_type == 'audio/mpeg') return 'audio';
        if ($attachment->file_mime_type == 'application/pdf') return 'pdf';
        if (str_contains($attachment->file_mime_type, 'image')) return 'image';
        return '';
    }

    public function getAttachmentModalSize()
    {

        if ($this->attachmentType == 'audio') {
            return 'w-3/4 h-1/2';
        }
        if ($this->attachmentType == 'pdf') {
            return 'w-5/6 lg:w-4/6 h-[80vh]';
        }
        if ($this->attachmentType == 'video') {
            return 'w-[80vw] h-[45vw]';
        }

        return 'w-5/6 lg:w-4/6';
    }

}