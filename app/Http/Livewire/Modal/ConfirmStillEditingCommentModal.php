<?php

namespace tcCore\Http\Livewire\Modal;

use tcCore\Http\Livewire\TCModalComponent;

class ConfirmStillEditingCommentModal extends TCModalComponent
{
    public $editingComment = '';

    public function render()
    {
        return view('livewire.modal.confirm-still-editing-comment-modal');
    }
}
