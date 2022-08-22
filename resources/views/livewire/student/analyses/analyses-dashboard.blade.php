<div id="dashboard-body"
     class="px-4 lg:px-8 xl:px-24 relative w-full pb-10"
     x-data="{}"
     x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10">
        <h1>{{ __('header.Analyses') }}</h1>
    </div>
    {{-- Filters--}}
    <div class="flex flex-col pt-4 pb-2">

        <div class="flex w-full items-center">
            <div class="flex flex-wrap w-full space-x-2 items-center" x-cloak>
                <x-input.choices-select :multiple="true"
                                        :options="$this->educationLevelYears"
                                        :withSearch="true"
                                        placeholderText="{{ __('general.Leerjaar')}}"
                                        wire:model="filters.subjects"
                                        wire:key="filter_eduction_level_years"
                                        filterContainer="analyses-active-filters"
                />

                <x-input.choices-select :multiple="true"
                                        :options="$this->periods"
                                        :withSearch="true"
                                        placeholderText="{{ __('teacher.Periode')}}"
                                        wire:model="filters.periods"
                                        wire:key="filter_periods"
                                        filterContainer="analyses-active-filters"
                />

                <x-input.choices-select :multiple="true"
                                        :options="$this->teachers"
                                        :withSearch="true"
                                        placeholderText="{{ __('general.Docent')}}"
                                        wire:model="filters.teachers"
                                        wire:key="filter_teacher"
                                        filterContainer="analyses-active-filters"
                />

            </div>


            @if($this->hasActiveFilters())
                <x-button.text-button class="ml-auto text-base"
                                      size="sm"
                                      @click="$dispatch('enable-loading-grid');document.getElementById('analyses-active-filters').innerHTML = '';"
                                      wire:click="clearFilters()"
                >
                    <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                    <x-icon.close-small/>
                </x-button.text-button>
            @else
                <x-button.text-button class="ml-auto text-base disabled"
                                      size="sm"
                                      disabled
                >
                    <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                    <x-icon.close-small/>
                </x-button.text-button>
            @endif
        </div>

        <div id="analyses-active-filters"
             x-data
             wire:key="filters-container"
             wire:ignore
             class="flex flex-wrap gap-2 mt-2 relative"
        >

        </div>
    </div>

</div>
