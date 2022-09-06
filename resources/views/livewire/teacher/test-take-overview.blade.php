<div x-data="{openTab: @entangle('openTab')}">
    <x-menu.tab.container>
        <x-menu.tab.item tab="taken" menu="openTab">
            {{ __('general.Mijn afgenomen toetsen') }}
        </x-menu.tab.item>
        <x-menu.tab.item tab="norm" menu="openTab">
            {{ __('general.Nakijken en normeren') }}
        </x-menu.tab.item>
    </x-menu.tab.container>

    <div class="flex w-full max-w-screen-2xl mx-auto  px-10">
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
                    <x-input.choices-select
                            wire:key="SchoolClasses_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->schoolClasses"
                            :withSearch="true"
                            placeholderText="{{ __('header.Klassen') }}"
                            wire:model="filters.{{ $this->openTab }}.school_class_id"
                            filterContainer="test-take-overview-{{ $this->openTab }}-active-filters"
                    />
                    <x-input.choices-select
                            wire:key="subjects_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->subjects"
                            :withSearch="true"
                            placeholderText="{{ __('toetsenbakker_toetsinvite.Vak') }}"
                            wire:model="filters.{{ $this->openTab }}.subject_id"
                            filterContainer="test-take-overview-{{ $this->openTab }}-active-filters"
                    />

                    <x-input.group>
                        <x-input.datepicker class="bg-offwhite"
                                            wire:model="filters.{{ $this->openTab }}.time_start_from"
                                            locale="{{ app()->getLocale() }}"
                                            placeholder="{{ __('teacher.Datum') }}"/>
                    </x-input.group>

                    <x-input.group>
                        <x-input.datepicker class="bg-offwhite"
                                            wire:model="filters.{{ $this->openTab }}.time_start_to"
                                            locale="{{ app()->getLocale() }}"
                                            placeholder="{{ __('teacher.Datum tot') }}"/>
                    </x-input.group>

                    @if($this->hasActiveFilters())
                        <x-button.text-button class="ml-auto text-base"
                                              size="sm"
                                              @click="$dispatch('enable-loading-grid');document.getElementById('test-take-overview-{{ $this->openTab }}-active-filters').innerHTML = '';"
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
                <div id="test-take-overview-{{ $this->openTab }}-active-filters" wire:ignore>
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
                            {{ $this->takenTestTakes->total() ?? 'geen' }} resultaten
                        {{--{{ trans_choice($this->getMessageKey($this->takenTestTakes->total()), $this->takenTestTakes->total(), ['count' => $this->takenTestTakes->total()]) }}--}}
                    </span>
                </div>
                <x-grid class="my-4">
                    @foreach(range(1, 6) as $value)
                        <x-grid.loading-card
                                :delay="$value"
                                wire:loading.class.remove="hidden"
                                wire:target="filters,clearFilters,$set"
                        />
                    @endforeach

                    @foreach($this->takenTestTakes as $testTake)
                        <x-grid.test-take-card :testTake="$testTake"/>
                    @endforeach
                </x-grid>
                {{ $this->takenTestTakes->links('components.partials.tc-paginator') }}

                <livewire:teacher.tests-overview-context-menu/>
            </div>
        </div>
    </div>

    <x-notification/>
</div>