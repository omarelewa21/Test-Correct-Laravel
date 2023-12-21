<?php

namespace tcCore\Http\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\WithPagination;
use tcCore\BaseSubject;
use tcCore\Subject;
use tcCore\TestAuthor;
use tcCore\UserSystemSetting;

abstract class OverviewComponent extends TCComponent
{
    use WithPagination;

    protected const PER_PAGE = 15;

    public array $filters = [];
    protected array $filterableAttributes = [];
    protected string $sessionKey = '';
    protected bool $storeFiltersInSession = true;

    public function mount()
    {
        $this->initialiseWithStoredData();
    }

    public function updatingFilters($value, $filter)
    {
        $this->resetPage();
    }

    public function updatedFilters($value, $filter)
    {
        $this->updateFiltersInSession($this->filters);
    }

    public function updatedPage($value)
    {
        $this->updatePageInSession($value);
    }

    protected function setFilters(array $filters = null): void
    {
        $this->filters = $filters ?? $this->filterableAttributes;
    }

    public function hasActiveFilters(): bool
    {
        return !empty($this->getCleanFilterForSearch());
    }

    public function clearFilters(?bool $clearSystemSettings = false): void
    {
        $this->setFilters();

        if ($clearSystemSettings) {
            UserSystemSetting::setSetting(auth()->user(), $this->getFilterSessionKey(), $this->filters);
        }
    }

    protected function getCleanFilterForSearch(): array
    {
        return collect($this->filters)->reject(fn($value) => blank($value))->toArray();
    }

    protected function getSessionKey(): string
    {
        if (filled($this->sessionKey)) {
            return $this->sessionKey;
        }

        return sprintf('%s-session', Str::kebab(class_basename(get_called_class())));
    }

    protected function getFilterSessionKey(): string
    {
        return $this->getSessionKey() . '-filters';
    }

    private function updateFiltersInSession(array $filters)
    {
        if ($this->storeFiltersInSession) {
            UserSystemSetting::setSetting(auth()->user(), $this->getFilterSessionKey(), $filters);
        }
    }

    private function updatePageInSession(int $value)
    {
        if ($this->storeFiltersInSession) {
            session()->put($this->getSessionKey() . '-page', $value);
        }
    }

    private function initialiseWithStoredData()
    {
        $this->initialiseStoredFilters();

        $this->initialiseStoredPage();
    }

    /**
     * @param mixed $sessionFilters
     * @return array
     */
    private function mergeStoredFiltersWithAllAvailable(mixed $sessionFilters): array
    {
        return array_merge($this->filterableAttributes, $sessionFilters);
    }

    /**
     * @return void
     */
    private function initialiseStoredFilters(): void
    {
        $sessionFilters = UserSystemSetting::getSetting(
            user        : auth()->user(),
            title       : $this->getFilterSessionKey(),
            sessionStore: true
        );

        if ($sessionFilters) {
            $sessionFilters = $this->mergeStoredFiltersWithAllAvailable($sessionFilters);
        }

        $this->setFilters($sessionFilters);
    }

    /**
     * @return void
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function initialiseStoredPage(): void
    {
        if ($page = session()->get($this->getSessionKey() . '-page', null)) {
            $this->setPage($page);
        }
    }

    public function getEducationLevelProperty()
    {
        return $this->filterableEducationLevelsBasedOnTab();
    }

    public function getBasesubjectsProperty()
    {
        if ($this->isExternalContentTab($this->openTab)) {
            return $this->getBaseSubjectsOptions();
        }
        return [];
    }

    private function getBaseSubjectsOptions()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            return BaseSubject::optionList();
        }

        return BaseSubject::whereIn(
            'id',
            Subject::filtered(['user_current' => Auth::id()], [])
                ->select('base_subject_id')
        )
            ->optionList();
    }

    public function getSubjectsProperty()
    {
        return Subject::filtered(['imp' => 0, 'user_id' => Auth::id()], ['name' => 'asc'])->optionList();
    }

    public function getEducationLevelYearProperty()
    {
        return array_map(fn($item) => ['value' => (int)$item, 'label' => (string)$item], range(1, 6));
    }

    public function getSharedSectionsAuthorsProperty()
    {
        return TestAuthor::schoolLocationAndSharedSectionsAuthorUsers(Auth::user())
            ->get()
            ->reject(function ($user) {
                return ($user->school_location_id === Auth::user()->school_location_id && $user->getKey() !== Auth::id(
                    ));
            })
            ->map(function ($user) {
                return ['value' => $user->id, 'label' => $user->nameFull];
            })
            ->values()
            ->toArray();
    }
}