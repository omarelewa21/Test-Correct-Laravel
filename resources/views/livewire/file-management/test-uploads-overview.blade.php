<div class="w-full" x-data>
    <div class="flex flex-col py-4 border-b border-secondary">
        <div class="flex w-full my-2">
            <div class="relative w-full">
                <x-input.text class="w-full search"
                              placeholder="{{ __('cms.Search...') }}"
                              wire:model.debounce.300ms="filters.search"
                />
                <x-icon.search class="absolute right-0 -top-2"/>
            </div>
        </div>
        <div class="flex w-full items-center">
            <div class="flex flex-wrap w-full space-x-2 items-center" x-cloak>
                <x-input.choices-select :multiple="true"
                                        :options="$this->teachers"
                                        :withSearch="true"
                                        placeholderText="{{ __('auth.Docent') }}"
                                        wire:model="filters.teacherid"
                                        wire:key="teachers"
                                        filterContainer="testuploads-active-filters"
                />
                <x-input.choices-select :multiple="true"
                                        :options="$this->statusses"
                                        :withSearch="true"
                                        placeholderText="{{ __('general.status') }}"
                                        wire:model="filters.status_ids"
                                        wire:key="statusses"
                                        filterContainer="testuploads-active-filters"
                />
                <x-input.datepicker class="bg-offwhite w-[170px]"
                                    wire:model="filters.created_at_start"
                                    locale="{{ app()->getLocale() }}"
                                    placeholder="{{ __('teacher.van') }}"/>

                <x-input.datepicker class="bg-offwhite w-[170px]"
                                    wire:model="filters.created_at_end"
                                    locale="{{ app()->getLocale() }}"
                                    placeholder="{{ __('teacher.Tot') }}"/>
            </div>

            <x-button.text-button class="ml-auto text-base"
                                  size="sm"
                                  wire:click="clearFilters()"
                                  :disabled="!$this->hasActiveFilters()"
            >
                <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                <x-icon.close-small/>
            </x-button.text-button>
        </div>

        <div id="testuploads-active-filters"
             x-data
             wire:key="filters-container"
             wire:ignore
             class="flex flex-wrap gap-2 mt-2 relative"
        >
        </div>
    </div>

    <x-partials.overview-content-section :results="$this->testUploads"
                                         :pagination="true"
    >
        <x-slot name="resultMessage">
            {{ trans_choice('general.number-of-results', $this->testUploads->total(), ['count' => $this->testUploads->total()]) }}
        </x-slot>

        <x-slot name="cards">
            @forelse($this->testUploads as $testUpload)
                <x-grid.file-management-card :file="$testUpload"/>
            @empty
                <span wire:loading.class="hidden"
                      wire:target="filters,clearFilters,$set">Helaas geen toetsies</span>
            @endforelse
        </x-slot>
    </x-partials.overview-content-section>
</div>
