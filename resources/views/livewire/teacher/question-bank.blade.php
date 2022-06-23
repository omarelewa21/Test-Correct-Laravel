<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto"
     x-data="{openTab: @entangle('openTab'), checkedCount: 0, inGroup: @entangle('inGroup')}"
     x-init="$watch('$store.questionBank.inGroup', value => inGroup = value);"
     @checked="$event.detail ? checkedCount += 1 : checkedCount -= 1"
     @question-added.window="Notify.notify('Vraag toegevoegd!');"
     @question-removed.window="Notify.notify('Vraag verwijderd!')"

>
    <div class="flex w-full border-b border-secondary">
        <div class="w-full max-w-5xl lg:max-w-7xl  mx-auto">
            <div class="flex w-full space-x-4 mx-8">
                <div>
                    <div class="flex relative hover:text-primary cursor-pointer"
                         @click="openTab = 'personal'"
                            {{--                         wire:click="setSource('personal')"--}}
                    >
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'personal' ? 'primary' : '' ">{{ __('general.Persoonlijk') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'personal' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative hover:text-primary cursor-pointer"
                         @click="openTab = 'school_location'"
                    >
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                              :class="openTab === 'school_location' ? 'primary' : '' ">Schoollocatie</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 'school_location' ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative text-midgrey cursor-default" title="{{ __('general.Later beschikbaar') }}">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 3 ? 'primary' : '' ">{{ __('general.Nationaal') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 3 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>

                <div>
                    <div class="flex relative text-midgrey cursor-default" title="{{ __('general.Later beschikbaar') }}">
                        {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 4">--}}
                        <span class="bold pt-[0.9375rem] pb-[0.8125rem]" :class="openTab === 4 ? 'primary' : '' ">{{ __('general.Examens') }}</span>
                        <span class="absolute w-full bottom-0" style="height: 3px"
                              :class="openTab === 4 ? 'bg-primary' : 'bg-transparent' "></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex w-full">
        <div class="w-full max-w-5xl lg:max-w-7xl mx-auto divide-y divide-secondary">
            <div class="mx-8">
                {{-- Filters--}}
                <div class="flex flex-col pt-4 pb-2">
                    <div class="flex w-full my-2">
                        <div class="relative w-full">
                            <x-input.text class="w-full"
                                          placeholder="Zoek..."
                                          wire:model.debounce.300ms="filters.{{ $this->openTab }}.search"
                            />
                            <x-icon.search class="absolute right-0 -top-2"/>
                        </div>
                    </div>
                    <div class="flex flex-wrap w-full space-x-2" x-cloak>
                        <x-input.choices-select :multiple="true"
                                                :options="$this->subjects"
                                                :withSearch="true"
                                                placeholderText="{{ __('student.subject')}}"
                                                wire:model="filters.{{ $this->openTab }}.subject_id"
                                                wire:key="subject_id_{{ $this->openTab }}"
                                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                        <x-input.choices-select :multiple="true"
                                                :options="$this->educationLevel"
                                                :withSearch="true"
                                                placeholderText="{{ __('general.Niveau')}}"
                                                wire:model="filters.{{ $this->openTab }}.education_level_id"
                                                wire:key="education_level_id_{{ $this->openTab }}"
                                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                        <x-input.choices-select :multiple="true"
                                                :options="$this->educationLevelYear"
                                                :withSearch="true"
                                                placeholderText="{{ __('general.Leerjaar')}}"
                                                wire:model="filters.{{ $this->openTab }}.education_level_year"
                                                wire:key="education_level_year_{{ $this->openTab }}"
                                                filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                        />
                        <span x-show="openTab !== 'personal'">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->authors"
                                            :withSearch="true"
                                            placeholderText="{{ __('general.Auteurs')}}"
                                            wire:model="filters.{{ $this->openTab }}.author_id"
                                            wire:key="author_id_{{ $this->openTab }}"
                                            filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                    />
                        </span>
                    </div>

                    <div id="questionbank-{{ $this->openTab }}-active-filters"
                         x-data
                         wire:key="filters-container-{{ $this->openTab }}"
                         wire:ignore
                         class="flex flex-wrap gap-2 mt-2"
                    >
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex flex-col py-4" style="min-height: 500px"
                     x-data="{filterLoading: false}"
                     x-init="
                    Livewire.hook('message.sent', (message, component) => {
                        if (component.el.id !== 'question-bank') {
                            return;
                        }
                        if (!livewireMessageContainsModelName(message, 'filter') && !livewireMessageContainsModelName(message, 'openTab')) {
                            return;
                        }
                        filterLoading = true;
                    })
                    Livewire.hook('message.processed', (message, component) => {
                        if (component.el.id !== 'question-bank') {
                            return;
                        }
                        if (!livewireMessageContainsModelName(message, 'filter') && !livewireMessageContainsModelName(message, 'openTab')) {
                            return;
                        }
                        filterLoading = false;
                    })
                 "
                >
                    <div class="flex">
                        <span class="note text-sm">{{ $this->resultCount }} resultaten</span>
                    </div>

                    <div class="mt-4 grid gap-6 grid-cols-1 lg:grid-cols-2" x-show="filterLoading" x-cloak>
                        @foreach(range(1,6) as $value)
                            <x-grid.loading-card :delay="$value"/>
                        @endforeach
                    </div>
                    <div class="mt-4 grid gap-6 grid-cols-1 lg:grid-cols-2" x-show="!filterLoading" x-cloak>
{{--                    <div class="mt-4 " x-show="!filterLoading" x-cloak>--}}
                        {{-- @TODO: Fix loading animation --}}
                        @foreach($this->questions as $question)
                            <x-grid.question-card :question="$question"/>
                        @endforeach

                        @if($this->questions->count() && $this->questions->count() != $this->resultCount)
                            @foreach(range(1, 6) as $delay)
                                <div class="animate-borderpulse border-6 rounded-10"
                                     style="min-height: 180px; height: 180px; animation-delay: calc({{ $delay }} * 200ms)">
                                    @if($delay === 4)
                                        <span x-data="{shouldSend: true}"
                                              x-init="$watch('shouldSend', value => {
                                                setTimeout(() => {
                                                    shouldSend = true
                                                }, 500);
                                          })"
                                              x-intersect.once="
                                            if (shouldSend) {
                                                $wire.showMore();
                                                shouldSend = false;
                                            }
                                          "
                                        ></span>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <span class="col-span-1 lg:col-span-2 text-center">
                            {{-- @TODO: Add translations--}}
                                @if(!$this->questions->count())
                                    @if($this->openTab === 'personal')
                                        U heeft nog geen eigen gemaakte vragen voor deze zoekfilters.
                                    @else
                                        Er is nog geen openbare content voor uw school.
                                    @endif
                                @else
                                    Er zijn geen items meer voor deze zoekfilters.
                                @endif
                        </span>
                        @endif
                    </div>
                </div>
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
        <x-button.cta class="main-shadow">
            <x-icon.checkmark/>
            <span>{{ __('cms.Toevoegen') }}</span>
        </x-button.cta>
    </div>
</div>