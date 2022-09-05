<div x-data="{openTab: @entangle('openTab')}">
    <x-menu.tab.container>
        <x-menu.tab.item tab="personal" menu="openTab">
            {{ __('general.Mijn afgenomen toetsen') }}
        </x-menu.tab.item>
        <x-menu.tab.item tab="school" menu="openTab">
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
{{--                    <x-input.choices-select--}}
{{--                            wire:key="education_level_year_{{ $this->openTab }}"--}}
{{--                            :multiple="true"--}}
{{--                            :options="$this->educationLevelYear"--}}
{{--                            :withSearch="true"--}}
{{--                            placeholderText="{{ __('general.Leerjaar') }}"--}}
{{--                            wire:model="filters.{{ $this->openTab }}.education_level_year"--}}
{{--                            filterContainer="questionbank-{{ $this->openTab }}-active-filters"--}}
{{--                    />--}}

{{--                    @if($this->hasActiveFilters())--}}
{{--                        <x-button.text-button class="ml-auto text-base"--}}
{{--                                              size="sm"--}}
{{--                                              @click="$dispatch('enable-loading-grid');document.getElementById('questionbank-{{ $this->openTab }}-active-filters').innerHTML = '';"--}}
{{--                                              wire:click="clearFilters('{{ $this->openTab }}')"--}}
{{--                                              wire:key="clearfilters-{{ $this->openTab }}"--}}
{{--                        >--}}
{{--                            <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>--}}
{{--                            <x-icon.close-small/>--}}
{{--                        </x-button.text-button>--}}
{{--                    @else--}}
{{--                        <x-button.text-button class="ml-auto text-base disabled"--}}
{{--                                              size="sm"--}}
{{--                                              disabled--}}
{{--                                              wire:key="clearfilters-disabled-{{ $this->openTab }}"--}}
{{--                        >--}}
{{--                            <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>--}}
{{--                            <x-icon.close-small/>--}}
{{--                        </x-button.text-button>--}}
{{--                    @endif--}}
                </div>
                <div id="questionbank-{{ $this->openTab }}-active-filters"
                     wire:ignore

                     x-data=""
                     :class="($el.childElementCount !== 1) ? 'mt-2' : ''"
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
                    <div class="flex space-x-2.5">
                        <x-button.cta class="px-4" wire:click="$emit('openModal', 'teacher.test-start-create-modal')">
                            <x-icon.plus-2/>
                            <span>{{ __('general.create test') }}</span>
                        </x-button.cta>
                    </div>
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
                        <x-grid.test-card :test="$test"/>
                    @endforeach
                </x-grid>
                {{ $results->links('components.partials.tc-paginator') }}

                <livewire:teacher.tests-overview-context-menu/>
            </div>
        </div>
    </div>

    <x-notification/>
</div>