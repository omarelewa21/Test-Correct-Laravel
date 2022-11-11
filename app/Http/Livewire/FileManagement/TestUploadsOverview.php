<?php

namespace tcCore\Http\Livewire\FileManagement;

use Illuminate\Support\Facades\Auth;
use Livewire\WithPagination;
use tcCore\BaseSubject;
use tcCore\FileManagement;
use tcCore\FileManagementStatus;
use tcCore\Http\Livewire\Overview\OverviewComponent;
use tcCore\Http\Traits\WithOverviewFilters;
use tcCore\Http\Traits\WithSorting;
use tcCore\User;

class TestUploadsOverview extends OverviewComponent
{
    use WithPagination, WithSorting, WithOverviewFilters;

    const PER_PAGE = 15;

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
            [$this->sortField => $this->sortDirection]
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

    public function getHandlersProperty()
    {
        return User::whereIn(
            'id',
            FileManagement::select('handledby')->distinct()
        )
            ->get()
            ->map(function (User $user) {
                return ['value' => $user->getKey(), 'label' => $user->name_full];
            });
    }

    public function getTestBuildersProperty()
    {
        return FileManagement::select('test_builder_code')
            ->whereNotNull('test_builder_code')
            ->distinct()
            ->get()
            ->map(fn ($builder) => ['value' => $builder->test_builder_code, 'label' => $builder->test_builder_code]);
    }

    private function setFilters(): void
    {
        $this->filters = [
            'search'            => '',
            'teacherid'         => [],
            'status_ids'        => [],
            'planned_at_start'  => '',
            'planned_at_end'    => '',
            'base_subjects'     => [],
            'handlerid'         => [],
            'test_builders' => [],
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
