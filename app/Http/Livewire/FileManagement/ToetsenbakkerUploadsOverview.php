<?php

namespace tcCore\Http\Livewire\FileManagement;

use tcCore\FileManagementStatus;

class ToetsenbakkerUploadsOverview extends TestUploadsOverview
{
    public function getStatussesProperty()
    {
        return FileManagementStatus::where('id', '<>', FileManagementStatus::STATUS_PROVIDED)->optionList();
    }

    public function render()
    {
        return view('livewire.file-management.test-uploads-overview')->layout('layouts.app-teacher');
    }
}