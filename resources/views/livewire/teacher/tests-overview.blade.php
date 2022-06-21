<div id="question-bank"
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
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"
     @question-added.window="Notify.notify('Vraag toegevoegd!')"
     @question-removed.window="Notify.notify('Vraag verwijderd!')"
>
    <div class="flex w-full border-b border-secondary">
        <div class="w-full max-w-5xl">
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
                              :class="openTab === 'national' ? 'primary' : '' ">
                            <span class="text-white  bg-mid-grey px-2 py-1 rounded-lg">{{ __('general.Scholengemeenschap') }}</span>
                            </span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'national' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative cursor-default">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]  "
                              :class="openTab === 'national' ? 'primary' : '' ">
                            <span class="text-white  bg-mid-grey px-2 py-1 rounded-lg">{{ __('general.Nationaal') }}</span>
                        </span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'national' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    {{--                    <div class="flex relative text-midgrey cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'exams'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'exams' ? 'primary' : '' ">{{ __('general.Examens') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'exams' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    {{--                    <div class="flex relative text-midgrey cursor-default">--}}
                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 'cito'">
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'cito' ? 'primary' : '' ">{{ __( 'general.cito-snelstart') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'cito' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="flex w-full">
        <div class="w-full mx-auto divide-y divide-secondary">
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

                    <x-input.choices-select
                            wire:key="subject_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->subjects"
                            :withSearch="true"
                            placeholderText="Vak"
                            wire:model="filters.{{ $this->openTab }}.subject_id"
                            filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                    />
                    <x-input.choices-select
                            wire:key="education_level_year_{{ $this->openTab }}"
                            :multiple="true"
                            :options="$this->educationLevelYear"
                            :withSearch="true"
                            placeholderText="Leerjaar"
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
                    <span class="note text-sm" wire:loading>{{  __('general.searching') }}</span>

                    <span class="note text-sm"
                          wire:loading.remove>{{  trans_choice('general.number-of-tests', $results->total(), ['count' => $results->total()]) }}</span>
                    <div class="flex space-x-2.5">
                                                <x-button.primary wire:click="$emitTo('navigation-bar', 'redirectToCake', 'planned.my_tests.plan')">
                                                    <x-icon.schedule/>
                                                    <span>{{ __('cms.Inplannen') }}</span>
                                                </x-button.primary>
                        <x-button.cta wire:click="$emit('openModal', 'teacher.test-start-create-modal')">
                            <x-icon.plus/>
                            <span>{{ __('general.create test') }}</span>
                        </x-button.cta>
                    </div>
                </div>
                <x-grid class="mt-4">
                    @foreach(range(1, 6) as $value)
                        <x-grid.loading-card :delay="$value"/>
                    @endforeach

                    @foreach($results as $test)
                        <x-grid.test-card wire:click="openTestDetail('{{ $test->uuid }}')" :test="$test" wire:loading.class="hidden"/>
                    @endforeach
                </x-grid>
                {{ $results->links('components.partials.tc-paginator') }}

            </div>
        </div>
    </div>
    <div class="hidden sticky h-0 bottom-20 ml-auto mr-4">
        <div class="flex justify-end mb-2">
            <span class="relative text-sm text-white rounded-full flex items-center justify-center main-shadow"
                  :class="checkedCount > 0 ? 'bg-primary' : 'bg-midgrey'"
                  style="min-width: 30px; height: 30px"
            >
                <span class="inline-flex -ml-px mt-px" x-text="checkedCount">0</span>
            </span>
        </div>
        <x-button.cta class="main-shadow" @click="loading = !loading; $root.classList.toggle('loading')">
            <x-icon.checkmark/>
            <span>{{ __('cms.Toevoegen') }}</span>
        </x-button.cta>
    </div>
    <livewire:teacher.test-delete-modal></livewire:teacher.test-delete-modal>
    <livewire:teacher.copy-test-from-schoollocation-modal></livewire:teacher.copy-test-from-schoollocation-modal>
    <x-notification/>
</div>
