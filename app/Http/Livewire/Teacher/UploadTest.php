<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\TemporaryLogin;

class UploadTest extends Component
{
    public string $formUuid;
    public array $typedetails = [
        'subject_id'           => 0,
        'education_level_id'   => 0,
        'education_level_year' => 0,
        'test_kind_id'         => 0,
    ];
    public $planned_at = null;
    public $name = 'Toetsnaam';
    public bool|null $contains_publisher_content = null;

    public bool $tabOneComplete = false;

    public function mount()
    {
        $this->formUuid = Uuid::uuid4();
    }

    public function updated()
    {

    }
    public function updatingContainsPublisherContent($value)
    {
        $this->contains_publisher_content = $value === 'yes';
    }

    public function render()
    {
        return view('livewire.teacher.upload-test')->layout('layouts.base');
    }

    public function back()
    {
        return redirect(TemporaryLogin::createForUser(Auth::user())->createCakeUrl());
    }
}