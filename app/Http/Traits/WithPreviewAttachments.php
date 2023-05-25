<?php


namespace tcCore\Http\Traits;

use tcCore\Attachment;
use tcCore\Http\Requests\Request;
use tcCore\QuestionAttachment;

trait WithPreviewAttachments
{
    public $attachment;
    protected $questionAttachment;
    public $audioCloseWarning = false;
    public $pressedPlay = false;
    public $timeout;
    public $questionId;
    public $attachmentType = '';
    public $blockAttachments = false;
    public $currentTimes = [];

    public function booted()
    {
        if($this->attachment) {
            $type = $this->attachmentBelongsToTypeQuestion($this->attachment);

            $id = $this->question->id;
            if($type=='group') {
                $id = $this->group->id;
            }

            $this->questionAttachment = $this->attachment->questionAttachments->where('question_id', $id)->first();
        }
    }

    public function showAttachment($attachmentUuid)
    {
        if($this->audioCloseWarning){
            return;
        }
        $this->attachment = Attachment::whereUuid($attachmentUuid)->first();
        $attachment = $this->attachment;
        $type = $this->attachmentBelongsToTypeQuestion($attachment);

        $this->questionId = $this->question->uuid;
        $id = $this->question->id;
        if($type=='group'){
            $this->questionId = $this->group->uuid;
            $id = $this->group->id;
        }

        $this->questionAttachment = $this->attachment->questionAttachments->where('question_id', $id)->first();
        $this->timeout = $this->questionAttachment->audioTimeoutTime();
        $this->attachmentType = $this->getAttachmentType($attachment);
    }

    public function closeAttachmentModal()
    {
        if (optional($this->attachment)->file_mime_type == 'audio/mpeg') {
            if ($this->audioIsPlayedAndCanBePlayedAgain() && !$this->audioCloseWarning) {
                if (!$this->questionAttachment->audioIsPausable()) {
                    $this->audioCloseWarning = true;
                    return;
                }
            }

            $this->dispatchBrowserEvent('pause-audio-player');

            if ($this->audioCloseWarning) {
                $this->questionAttachment->audioIsPlayedOnce();
                $this->audioCloseWarning = false;
            }
            if ($this->timeout != null) {
                $data = ['timeout' => $this->timeout, 'attachment' => $this->attachment->getKey()];
                $this->dispatchBrowserEvent('start-timeout', $data);
            }
        }

        $this->questionAttachment = null;
        $this->attachment = null;
    }

    public function audioIsPlayedOnce()
    {

    }

    public function audioStoreCurrentTime($attachmentUuid, $currentTime)
    {
        $this->currentTimes[$attachmentUuid] = $currentTime;
    }

    public function getCurrentTime()
    {
        if(array_key_exists($this->attachment->uuid,$this->currentTimes)){
            return $this->currentTimes[$this->attachment->uuid];
        }
        return 0;
    }

    public function updating(&$value)
    {
        Request::filter($value);
    }

    private function audioIsPlayedAndCanBePlayedAgain()
    {
        return $this->questionAttachment->audioIsOnlyPlayableOnce()
            && $this->questionAttachment->audioCanBePlayedAgain()
            && ($this->questionAttachment->audioHasCurrentTime()
                || $this->pressedPlay);
    }

    public function updateAnswerIdForTestParticipant()
    {
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

        // return 'w-5/6 lg:w-4/6';
    }

    private function attachmentBelongsToTypeQuestion($attachment)
    {
        if(is_null($this->group)){
            return 'question';
        }
        $questions = $attachment->questions()->where('question_id',$this->group->getKey());
        if($questions->count()>0){
            return 'group';
        }
        return 'question';
    }

    public function registerExpirationTime() {}
}