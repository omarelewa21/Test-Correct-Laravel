<div id="testbank"
     x-data="{
        openTab: @entangle('openTab'),
         checkedCount: 0,
         loading: false,
         activateCard: (current) => {
            document.querySelectorAll('.grid-card').forEach(el => {
                el == current
                ? el.classList.add('text-primary')
                : el.classList.remove('text-primary');
            });
         }
     }"
     wire:init="handleReferrerActions()"
     class="flex flex-col  w-full min-h-full bg-lightGrey border-t border-secondary top-0"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"
     @question-added.window="Notify.notify('Vraag toegevoegd!')"
     @question-removed.window="Notify.notify('Vraag verwijderd!')"
>

    <div class="border-b border-secondary sticky sticky-pseudo-bg bg-lightGrey z-1"
         style="transition: top 0.3s linear;top: 150px"
         @tiles-hidden.window="$el.style.top = '100px'"
         @tiles-shown.window="$el.style.top = '150px'"
    >
        <div class="w-full max-w-screen-2xl px-10">
            <div class="flex w-full space-x-4">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'personal'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'personal' ? 'primary' : '' ">{{ __('general.Persoonlijk') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'personal' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    {{--                    <div class="flex relative text-midgrey cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'school'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'school' ? 'primary' : '' ">{{ __('general.School') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'school' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative cursor-default">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]  "
                              :class="openTab === 'umbrella' ? 'primary' : '' ">
                            <span class="text-white  bg-mid-grey px-2 py-1 rounded-lg">{{ __('general.Scholengemeenschap') }}</span>
                            </span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'umbrella' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                @if(auth()->user()->schoolLocation->show_national_item_bank)
                <div>
{{--                    <div class="flex relative cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'national'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]  "
                              :class="openTab === 'national' ? 'primary' : '' ">{{ __('general.Nationaal') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'national' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    <div class="flex w-full max-w-screen-2xl mx-auto  px-10">
        <div class="w-full divide-y divide-secondary">
            {{-- Filters--}}
            <div class="flex flex-col py-4">
                <div class="flex w-full mt-2">
                    <div class="relative w-full">
                        <x-input.text class="w-full"
                                      placeholder="Zoek..."
                                      wire:model="filters.{{ $this->openTab }}.name"
                        />
                        <x-icon.search class="absolute right-0 -top-2"/>
                    </div>
                </div>
                <div class="flex flex-wrap w-full gap-2 mt-2">

                    @if ($this->openTab === 'national')
                        <x-input.choices-select
                                wire:key="base_subject_{{ $this->openTab }}"
                                :multiple="true"
                                :options="$this->basesubjects"
                                :withSearch="true"
                                placeholderText="{{ __('Categorie') }}"
                                wire:model="filters.{{ $this->openTab }}.base_subject_id"
                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                    @else
                        <x-input.choices-select
                                wire:key="subject_{{ $this->openTab }}"
                                :multiple="true"
                                :options="$this->subjects"
                                :withSearch="true"
                                placeholderText="Vak"
                                wire:model="filters.{{ $this->openTab }}.subject_id"
                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                    @endif
                    <x-input.choices-select
                            wire:key="education_level_year_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->educationLevelYear"
                            :withSearch="true"
                            placeholderText="{{ __('Leerjaar') }}"
                            wire:model="filters.{{ $this->openTab }}.education_level_year"
                            filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                    />
                    <x-input.choices-select
                            wire:key="educationLevel_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->educationLevel"
                            :withSearch="true"
                            placeholderText="{{ __('Niveau') }}"
                            wire:model="filters.{{ $this->openTab }}.education_level_id"
                            filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                    />
                    @if ($this->openTab !== 'personal')
                        <x-input.choices-select
                                wire:key="authors_{{ $this->openTab }}"
                                :multiple="true"
                                :options="$this->authors"
                                :withSearch="true"
                                placeholderText="{{ __('Auteurs') }}"
                                wire:model="filters.{{ $this->openTab }}.author_id"
                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                    @endif

                    @if($this->hasActiveFilters())
                        <x-button.text-button class="ml-auto text-base"
                                              size="sm"
                                              @click="$dispatch('enable-loading-grid');document.getElementById('questionbank-{{ $this->openTab }}-active-filters').innerHTML = '';"
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
                <div id="questionbank-{{ $this->openTab }}-active-filters"
                     wire:ignore

                     x-data=""
                     :class="($el.childElementCount !== 1) ? 'mt-2' : ''"
                     class="flex flex-wrap gap-2"
                >
                </div>
            </div>

            {{-- Content --}}
            <div class="flex flex-col py-4" style="min-height: 500px">
                <div class="flex justify-between">
                    <span class="note text-sm" wire:loading  wire:target="filters,clearFilters,$set">{{  __('general.searching') }}</span>

                    <span class="note text-sm"
                          wire:loading.remove  wire:target="filters,clearFilters,$set">{{  trans_choice('general.number-of-tests', $results->total(), ['count' => $results->total()]) }}</span>
                    <div class="flex space-x-2.5">
{{--                        <x-button.primary--}}
{{--                                wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">--}}
{{--                            <x-icon.schedule/>--}}
{{--                            <span>{{ __('cms.Inplannen') }}</span>--}}
{{--                        </x-button.primary>--}}
                        <x-button.cta wire:click="$emit('openModal', 'teacher.test-start-create-modal')">
                            <x-icon.plus/>
                            <span>{{ __('general.create test') }}</span>
                        </x-button.cta>
                    </div>
                </div>
                <x-grid class="mt-4">
                    @foreach(range(1, 6) as $value)
                        <x-grid.loading-card
                                :delay="$value"
                                wire:loading.class.remove="hidden"
                                wire:target="filters,clearFilters,$set"
                        />
                    @endforeach

                    @foreach($results as $test)
                        <x-grid.test-card :test="$test" />
                    @endforeach
                </x-grid>
                {{ $results->links('components.partials.tc-paginator') }}

            </div>
        </div>
    </div>
    <livewire:teacher.test-delete-modal></livewire:teacher.test-delete-modal>
    <livewire:teacher.copy-test-from-schoollocation-modal></livewire:teacher.copy-test-from-schoollocation-modal>
    <x-notification/>
    <livewire:teacher.tests-overview-context-menu></livewire:teacher.tests-overview-context-menu>
</div>
