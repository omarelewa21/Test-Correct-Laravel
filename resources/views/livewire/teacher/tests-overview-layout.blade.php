@yield('container')
<x-menu.tab.container :withTileEvents="$usesTileMenu ?? true">
    <x-menu.tab.item tab="personal" menu="openTab" :when="!$this->isExamCoordinator">
        {{ __('general.Persoonlijk') }}
    </x-menu.tab.item>
    <x-menu.tab.item tab="school_location" menu="openTab">
        {{ __('general.School') }}
    </x-menu.tab.item>
    <x-menu.tab.item tab="umbrella" menu="openTab" :when="$allowedTabs->contains('umbrella')">
        {{ __('general.Scholengemeenschap') }}
    </x-menu.tab.item>
    <x-menu.tab.item tab="national" menu="openTab" :highlight="true" :when="$allowedTabs->contains('national')">
        {{ __('general.Nationaal') }}
    </x-menu.tab.item>
    <x-menu.tab.item tab="creathlon" menu="openTab" :highlight="true" :when="$allowedTabs->contains('creathlon')">
        {{ __('general.Creathlon') }}
    </x-menu.tab.item>
</x-menu.tab.container>

<div class="flex w-full max-w-screen-2xl mx-auto  px-8" @hasSection('cms-js-properties')
    @yield('cms-js-properties')
        @endif>
    <div class="w-full divide-y divide-secondary z-0">
        {{-- Filters--}}
        <div class="flex flex-col py-4">
            <div class="flex w-full mt-2">
                <div class="relative w-full">
                    <x-input.group class="w-full">
                        <x-input.text class="w-full"
                                      placeholder="{{ __('cms.Search...') }}"
                                      wire:model="filters.name"
                        />
                        <x-icon.search class="absolute right-0 -top-2"/>
                    </x-input.group>
                </div>
            </div>
            <div class="flex flex-wrap w-full gap-2 mt-2">
                @if ($this->isExternalContentTab())
                    <x-input.choices-select
                            wire:key="base_subject_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->basesubjects"
                            :withSearch="true"
                            placeholderText="{{ __('general.Categorie') }}"
                            wire:model="filters.base_subject_id"
                            filterContainer="testbank-{{ $this->openTab }}-active-filters"
                    />
                @else
                    <x-input.choices-select
                            wire:key="subject_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->subjects"
                            :withSearch="true"
                            placeholderText="{{ __('student.subject') }}"
                            wire:model="filters.subject_id"
                            filterContainer="testbank-{{ $this->openTab }}-active-filters"
                    />
                @endif
                <x-input.choices-select
                        wire:key="education_level_year_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->educationLevelYear"
                        :withSearch="true"
                        placeholderText="{{ __('general.Leerjaar') }}"
                        wire:model="filters.education_level_year"
                        filterContainer="testbank-{{ $this->openTab }}-active-filters"
                />
                <x-input.choices-select
                        wire:key="educationLevel_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->educationLevel"
                        :withSearch="true"
                        placeholderText="{{ __('general.Niveau') }}"
                        wire:model="filters.education_level_id"
                        filterContainer="testbank-{{ $this->openTab }}-active-filters"
                />
                <x-input.choices-select
                        wire:key="taxonomy_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->taxonomies"
                        :withSearch="true"
                        :sortOptions="false"
                        placeholderText="{{ __('cms.Taxonomie') }}"
                        wire:model="filters.taxonomy"
                        filterContainer="testbank-{{ $this->openTab }}-active-filters"
                />

                @if ($this->canFilterOnAuthors())
                    @if($this->openTab === 'umbrella')
                        <x-input.choices-select
                                wire:key="shared_authors_{{ $this->openTab }}"
                                :multiple="true"
                                :options="$this->sharedSectionsAuthors"
                                :withSearch="true"
                                placeholderText="{{ __('general.Auteurs') }}"
                                wire:model="filters.shared_sections_author_id"
                                filterContainer="testbank-{{ $this->openTab }}-active-filters"
                        />
                    @else
                        <x-input.choices-select
                                wire:key="authors_{{ $this->openTab }}"
                                :multiple="true"
                                :options="$this->authors"
                                :withSearch="true"
                                placeholderText="{{ __('general.Auteurs') }}"
                                wire:model="filters.author_id"
                                filterContainer="testbank-{{ $this->openTab }}-active-filters"
                        />
                    @endif
                @endif

                @yield('clear-filters-button')
            </div>
            <div id="testbank-{{ $this->openTab }}-active-filters"
                 wire:ignore
                 wire:key="tb-filters-container-{{ $this->openTab }}"
                 x-data=""
                 class="flex flex-wrap gap-2 mt-2 relative"
            >
            </div>
        </div>

        {{-- Content --}}
        <x-partials.overview-content-section :$results :pagination="true">
            <x-slot name="resultMessage">
                {{ trans_choice($this->getMessageKey($results->total()), $results->total(), ['count' => $results->total()]) }}
            </x-slot>

            <x-slot name="header">
                @hasSection('create-test-button')
                    @yield('create-test-button')
                @endif
            </x-slot>

            <x-slot name="cards">
                @foreach($results as $test)
                    <x-grid.test-card :test="$test" :mode="$cardMode ?? 'page'"/>
                @endforeach
            </x-slot>

            <livewire:context-menu.test-card/>
        </x-partials.overview-content-section>

    </div>
</div>
@hasSection('detailSlide')
    @yield('detailSlide')
@endif
<x-notification/>
<x-after-planning-toast/>
</div> {{-- This closing tag closes the container that is included on line 1 --}}
