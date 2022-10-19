<?php


namespace tcCore\Http\Traits;


use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use tcCore\Attachment;
use tcCore\QuestionAttachment;

trait WithAttachments
{
    public $attachment;
    protected $questionAttachment;
    public $audioCloseWarning = false;
    public $pressedPlays = [];
    public $timeout;
    public $questionId;
    public $answerId;
    public $attachmentType = '';
    public $positionTop;
    public $positionLeft;
    public $blockAttachments = false;


    public $currentTimes = [];
    public $playedOnce = [];
    public $playedTotalAudio = [];
    public $reinitializedTimeoutData;

    public function mountWithAttachments()
    {
        $this->answerId = $this->answers[$this->question->uuid]['uuid'];
        $this->question->loadMissing('attachments');
        $this->checkAttachmentTimeoutInSession();
    }

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

    public function showAttachment($attachment)
    {
        if($this->audioCloseWarning){
            return;
        }
        $this->attachment = Attachment::whereUuid($attachment)->first();
        $type = $this->attachmentBelongsToTypeQuestion($this->attachment);
        $this->questionId = $this->question->id;
        if($type=='group'){
            $this->questionId = $this->group->id;
        }
        $this->questionAttachment = $this->attachment->questionAttachments->where('question_id', $this->questionId)->first();
        $this->timeout = $this->questionAttachment->audioTimeoutTime();
        $this->attachmentType = $this->getAttachmentType($this->attachment);
    }

    public function closeAttachmentModal()
    {
        if (optional($this->attachment)->file_mime_type == 'audio/mpeg') {
            if ($this->audioHasTimerAndIsStartedAndNotFinished()&& !$this->audioCloseWarning){
                if ($this->questionAttachment->audioIsPausable()) {
                    $this->dispatchBrowserEvent('pause-audio-player');
                }
                $this->audioCloseWarning = true;
                return;
            }

            if ($this->audioOnlyPlayOnceAndIsStartedAndNotFinished() && !$this->audioCloseWarning) {
                if (!$this->questionAttachment->audioIsPausable()) {
                    $this->audioCloseWarning = true;
                    return;
                }
            }

            if ($this->audioCloseWarning&&$this->questionAttachment->audioOnlyPlayOnce()) {
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

        $this->questionAttachment = null;
        $this->attachment = null;
    }

    public function audioIsPlayedOnce()
    {
        $this->playedOnce[] = $this->attachment->uuid;
        $this->questionAttachment->audioIsPlayedOnce();
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
        return $this->questionAttachment->audioOnlyPlayOnce()
            && $this->audioCanBePlayedAgain()
            && ($this->questionAttachment->audioHasCurrentTime()
                || $this->playStarted());
    }

    private function audioHasTimerAndIsStartedAndNotFinished()
    {
        return $this->questionAttachment->hasAudioTimeout()
                && !$this->totalAudioPlayed()
                && ($this->questionAttachment->audioHasCurrentTime()
                || $this->playStarted());
    }

    private function audioCanBePlayedAgain()
    {
        if(!$this->questionAttachment->audioCanBePlayedAgain()){
            return false;
        }
        if(in_array($this->questionAttachment->uuid, $this->playedOnce)){
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
        if ($this->attachmentType == 'pdf'&&(!is_null(Auth::user())&&Auth::user()->text2speech)) {
            return 'w-5/6 lg:w-5/6 h-[80vh]';
        }
        if ($this->attachmentType == 'pdf') {
            return 'w-5/6 lg:w-4/6 h-[80vh]';
        }
        if ($this->attachmentType == 'video') {
            return 'w-[80vw] h-[45vw]';
        }

        return 'w-5/6 lg:w-4/6';
    }

    private function checkAttachmentTimeoutInSession()
    {
        if ($expirationInfo = session()->get('question_timeout_expiration_info'.$this->answerId, null)) {
            $expirationTimeLeft = Carbon::parse($expirationInfo['expires']);

            if($expirationTimeLeft->isBefore(Carbon::now())) {
                session()->forget('question_timeout_expiration_info'.$this->answerId);
                $this->closeQuestion();
                return false;
            }

            $this->blockAttachments = true;
            $this->reinitializedTimeoutData = [
                'timeout' => $expirationInfo['timeoutInSeconds'],
                'timeLeft' => $expirationTimeLeft->diffInSeconds(Carbon::now()),
                'attachment' => $expirationInfo['attachmentId'],
            ];
        }
        return true;
    }

    public function registerExpirationTime($attachmentId)
    {
        $this->blockAttachments = true;

        $expirationTime = Carbon::now()->addSeconds($this->timeout);
        $uuid = $this->answerId;

        session()->put(
            'question_timeout_expiration_info'.$uuid,
            [
                'expires' => $expirationTime,
                'timeoutInSeconds' => $this->timeout,
                'attachmentId' => $attachmentId,
            ]
        );
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
}