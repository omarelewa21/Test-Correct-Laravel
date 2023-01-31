<?php

namespace tcCore\Http\Livewire\Modal;

use LivewireUI\Modal\ModalComponent;
use tcCore\Attachment;

class PreviewAttachment extends Preview
{
    public Attachment $attachment;
    public string $attachmentType;
    public ?string $iconComponentName = null;
    public string $questionUuid;

    public $currentTimes = [];
    public $pressedPlay = false;

    public function mount(string $attachmentUuid, string $questionUuid) {
        $this->attachment = Attachment::whereUuid($attachmentUuid)->first();
        $this->questionUuid = $questionUuid;

        $this->setProperties();
    }

    public function render() {
        return view('livewire.modal.preview-attachment');
    }

    protected function setProperties() {
        $this->attachmentType = $this->attachment->getFileType();
        $iconNameSuffix = $this->attachmentType;

        $this->setTitle();

        if($this->attachmentType === 'video') {
            $iconNameSuffix = Attachment::getVideoHost($this->attachment->link);
        }
        if($this->attachmentType === 'image') {
            /*$this->imageDimensions = collect(getimagesize($this->attachment->getCurrentPath()))
                ->reduce(function ($carry, $item, $key) {
                    if($key === 0) {
                        $carry['width'] = $item;
                    }
                    if($key === 1) {
                        $carry['height'] = $item;
                    }
                    return $carry;
                }, collect());*/
        }


        $this->iconComponentName = sprintf('icon.%s', $iconNameSuffix);
    }

    protected function setTitle() {
        $this->title = $this->attachment->title;
    }




    public function audioIsPlayedOnce() {}

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
}
