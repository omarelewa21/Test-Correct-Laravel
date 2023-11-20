<div class="flex flex-col py-4">
    <div class="flex w-full mt-2">
        <div class="relative flex w-full">
            <x-input.group class="w-full">
                <x-input.text class="w-full"
                              placeholder="{{ __('cms.Search...') }}"
                              wire:model="{{ $searchProperty }}"
                />
                <x-icon.search class="absolute right-0 -top-2" />
            </x-input.group>
        </div>
    </div>
    <div class="flex flex-wrap w-full gap-2 mt-2">
        @if ($this->isExternalContentTab())
            <x-input.choices-select
                    wire:key="{{ $versionablePrefix }}_base_subject_{{ $this->openTab }}"
                    :multiple="true"
                    :options="$this->basesubjects"
                    :withSearch="true"
                    placeholderText="{{ __('general.Categorie') }}"
                    wire:model="filters.base_subject_id"
                    :filterContainer="$activeFilterContainer"
            />
        @else
            <x-input.choices-select
                    wire:key="{{ $versionablePrefix }}_subject_{{ $this->openTab }}"
                    :multiple="true"
                    :options="$this->subjects"
                    :withSearch="true"
                    placeholderText="{{ __('student.subject') }}"
                    wire:model="filters.subject_id"
                    :filterContainer="$activeFilterContainer"
            />
        @endif
        <x-input.choices-select
                wire:key="{{ $versionablePrefix }}_education_level_year_{{ $this->openTab }}"
                :multiple="true"
                :options="$this->educationLevelYear"
                :withSearch="true"
                placeholderText="{{ __('general.Leerjaar') }}"
                wire:model="filters.education_level_year"
                :filterContainer="$activeFilterContainer"
        />
        <x-input.choices-select
                wire:key="{{ $versionablePrefix }}_educationLevel_{{ $this->openTab }}"
                :multiple="true"
                :options="$this->educationLevel"
                :withSearch="true"
                placeholderText="{{ __('general.Niveau') }}"
                wire:model="filters.education_level_id"
                :filterContainer="$activeFilterContainer"
        />

        @if($this->canFilterOnAuthors())
            @if($this->openTab === 'umbrella')
                <x-input.choices-select
                        wire:key="{{ $versionablePrefix }}_shared_authors_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->sharedSectionsAuthors"
                        :withSearch="true"
                        placeholderText="{{ __('general.Auteurs') }}"
                        wire:model="filters.shared_sections_author_id"
                        :filterContainer="$activeFilterContainer"
                />
            @else
                <x-input.choices-select
                        wire:key="{{ $versionablePrefix }}_authors_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->users"
                        :withSearch="true"
                        placeholderText="{{ __('general.Auteurs') }}"
                        wire:model="filters.user_id"
                        :filterContainer="$activeFilterContainer"
                />
            @endif
        @endif

        <x-button.text class="ml-auto text-base"
                       size="sm"
                       x-on:click="clearFilters()"
                       :disabled="!$this->hasActiveFilters()"
        >
            <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
            <x-icon.close-small />
        </x-button.text>
    </div>
    <div id="{{ $activeFilterContainer }}"
         wire:ignore
         wire:key="{{ $versionablePrefix }}-filters-container-{{ $this->openTab }}"
         class="flex flex-wrap gap-2 mt-2 relative"
    >
    </div>
</div>
