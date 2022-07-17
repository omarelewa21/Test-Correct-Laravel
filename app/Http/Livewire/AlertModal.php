<?php

namespace tcCore\Http\Livewire;

use LivewireUI\Modal\ModalComponent;

class AlertModal extends ModalComponent
{
    public $message;
    public $primaryAction = false;
    public $type = 'warning';
    public $primaryActionBtnLabel = false;
    public $title = false;

    public function mount($message, $title, $primaryAction=false, $primaryActionBtnLabel = false, $type = 'warning') {
        $this->message = $message;
        $this->title = $title;
        $this->type = $type;
        $this->primaryAction = $primaryAction;
        $this->primaryActionBtnLabel = $primaryActionBtnLabel;
    }

    public function render()
    {
        return view('livewire.alert-modal');
    }
}
