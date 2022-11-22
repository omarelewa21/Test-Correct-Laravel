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

<div class="flex w-full max-w-screen-2xl mx-auto  px-8" @hasSection('cms-js-properties') @yield('cms-js-properties') @endif>
    <div class="w-full divide-y divide-secondary">
        {{-- Filters--}}
        <div class="flex flex-col py-4">
            <div class="flex w-full mt-2">
                <div class="relative w-full">
                    <x-input.group class="w-full">
                        <x-input.text class="w-full"
                                      placeholder="{{ __('cms.Search...') }}"
                                      wire:model="filters.{{ $this->openTab }}.name"
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
                            wire:model="filters.{{ $this->openTab }}.base_subject_id"
                            filterContainer="testbank-{{ $this->openTab }}-active-filters"
                    />
                @else
                    <x-input.choices-select
                            wire:key="subject_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->subjects"
                            :withSearch="true"
                            placeholderText="{{ __('student.subject') }}"
                            wire:model="filters.{{ $this->openTab }}.subject_id"
                            filterContainer="testbank-{{ $this->openTab }}-active-filters"
                    />
                @endif
                <x-input.choices-select
                        wire:key="education_level_year_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->educationLevelYear"
                        :withSearch="true"
                        placeholderText="{{ __('general.Leerjaar') }}"
                        wire:model="filters.{{ $this->openTab }}.education_level_year"
                        filterContainer="testbank-{{ $this->openTab }}-active-filters"
                />
                <x-input.choices-select
                        wire:key="educationLevel_{{ $this->openTab }}"
                        :multiple="true"
                        :options="$this->educationLevel"
                        :withSearch="true"
                        placeholderText="{{ __('general.Niveau') }}"
                        wire:model="filters.{{ $this->openTab }}.education_level_id"
                        filterContainer="testbank-{{ $this->openTab }}-active-filters"
                />
                @if ($this->canFilterOnAuthors())
                    <x-input.choices-select
                            wire:key="authors_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->authors"
                            :withSearch="true"
                            placeholderText="{{ __('general.Auteurs') }}"
                            wire:model="filters.{{ $this->openTab }}.author_id"
                            filterContainer="testbank-{{ $this->openTab }}-active-filters"
                    />
                @endif

                @if($this->hasActiveFilters())
                    <x-button.text-button class="ml-auto text-base"
                                          size="sm"
                                          @click="$dispatch('enable-loading-grid');document.getElementById('testbank-{{ $this->openTab }}-active-filters').innerHTML = '';"
                                          wire:click="clearFilters('{{ $this->openTab }}')"
                                          wire:key="clearfilters-{{ $this->openTab }}"
                    >
                        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                        <x-icon.close-small/>
                    </x-button.text-button>
                @else
                    <x-button.text-button class="ml-auto text-base disabled"
                                          size="sm"
                                          disabled
                                          wire:key="clearfilters-disabled-{{ $this->openTab }}"
                    >
                        <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                        <x-icon.close-small/>
                    </x-button.text-button>
                @endif
            </div>
            <div id="testbank-{{ $this->openTab }}-active-filters"
                 wire:ignore
                 x-data=""
                 :class="{'mt-2': $el.childElementCount > 0}"
                 class="flex flex-wrap gap-2"
            >
            </div>
        </div>

        {{-- Content --}}
        <div class="flex flex-col pt-4 pb-16" style="min-height: 500px">
            <div class="flex justify-between">
                    <span class="note text-sm" wire:loading
                          wire:target="filters,clearFilters,$set">{{  __('general.searching') }}</span>

                <span class="note text-sm"
                      wire:loading.remove
                      wire:target="filters,clearFilters,$set">
                        {{ trans_choice($this->getMessageKey($results->total()), $results->total(), ['count' => $results->total()]) }}
                    </span>
                @hasSection('create-test-button')
                    @yield('create-test-button')
                @endif
            </div>
            <x-grid class="my-4">
                @foreach(range(1, 6) as $value)
                    <x-grid.loading-card
                            :delay="$value"
                            wire:loading.class.remove="hidden"
                            wire:target="filters,clearFilters,$set"
                    />
                @endforeach

                @foreach($results as $test)
                    <x-grid.test-card :test="$test" :mode="$cardMode ?? 'page'"/>
                @endforeach
            </x-grid>
            {{ $results->links('components.partials.tc-paginator') }}

            <livewire:context-menu.test-card/>
        </div>
    </div>
</div>
@hasSection('detailSlide')
    @yield('detailSlide')
@endif
<x-notification/>
<x-after-planning-toast/>
</div> {{-- This closing tag closes the container that is included on line 1 --}}
