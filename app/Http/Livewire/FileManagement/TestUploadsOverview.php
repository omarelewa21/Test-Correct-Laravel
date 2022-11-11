<?php

namespace tcCore\Http\Livewire\FileManagement;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\BaseSubject;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\User;

class TestUploadsOverview extends Component
{
    use WithPagination;

    const PER_PAGE = 12;

    public array $filters;

    public function mount()
    {
        $this->setFilters();
    }

    public function render()
    {
        return view('livewire.file-management.test-uploads-overview')->layout('layouts.app-admin');
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function getTestUploadsProperty()
    {
        return FileManagement::filtered(
            Auth::user(),
            $this->getCleanFilters() + ['type' => FileManagement::TYPE_TEST_UPLOAD],
            ['id' => 'desc']
        )
            ->with([
                'status:id,name,colorcode',
                'schoolLocation:id,name',
                'teacher:id,name,name_first,name_suffix',
                'handler:id,name,name_first,name_suffix',
            ])
            ->paginate(self::PER_PAGE);
    }

    public function getTeachersProperty()
    {
        return FileManagement::getBuilderForUsers(Auth::user(), FileManagement::TYPE_TEST_UPLOAD)
            ->get()
            ->map(function (User $user) {
                return ['value' => $user->getKey(), 'label' => $user->getFullNameWithAbbreviatedFirstName()];
            });
    }

    public function getStatussesProperty()
    {
        return FileManagementStatus::optionList();
    }

    public function getBaseSubjectsProperty()
    {
        return BaseSubject::optionList();
    }

    private function setFilters(): void
    {
        $this->filters = [
            'search'           => '',
            'teacherid'        => [],
            'status_ids'       => [],
            'planned_at_start' => '',
            'planned_at_end'   => '',
            'base_subjects'    => [],
        ];
    }

    private function getCleanFilters(): array
    {
        return collect($this->filters)->reject(fn($value) => blank($value))->toArray();
    }

    public function clearFilters(): void
    {
        $this->dispatchBrowserEvent('clear-datepicker');
        $this->setFilters();
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->getCleanFilters());
    }

    public function openDetail(FileManagement $file)
    {
        return $file->redirectToDetail();
    }
}
