<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto overflow-x-hidden"
     x-data="{openTab: @entangle('openTab'), inGroup: @entangle('inGroup'), groupDetail: null, bodyVisibility: true,  maxHeight: 'calc(100vh - var(--header-height))'}"
     :style="`max-height: ${maxHeight}`"
     x-init="
        groupDetail = $el.querySelector('#groupdetail');
        $watch('$store.questionBank.inGroup', value => inGroup = value);
        $watch('$store.questionBank.active', value => {
           value ? $wire.render() : closeGroupDetail();
        });
        showGroupDetails = async (groupQuestionUuid, inTest = false) => {
            let readyForSlide = await $wire.showGroupDetails(groupQuestionUuid, inTest);

            if (readyForSlide) {
                groupDetail.style.left = 0;
                $el.closest('.drawer').scrollTo({top: 0, behavior: 'smooth'});
                $el.scrollTo({top: 0, behavior: 'smooth'});
                maxHeight = groupDetail.offsetHeight + 'px';
                $nextTick(() => {
                    setTimeout(() => bodyVisibility = false, 250);
                    handleVerticalScroll($el.closest('.slide-container'));
                })

            }
        }

        closeGroupDetail = () => {
            if (!bodyVisibility) {
                bodyVisibility = true;
                maxHeight = 'calc(100vh - var(--header-height))';
                groupDetail.style.left = '100%';
                $nextTick(() => {
                    $wire.clearGroupDetails();
                    setTimeout(() => {
                        handleVerticalScroll($el.closest('.slide-container'));
                    }, 250);
                })
            }

        }
        addQuestionToTest = async (button, questionUuid) => {
            button.disabled = true
            var enableButton = await $wire.handleCheckboxClick(questionUuid);
            if (enableButton) button.disabled = false;
        }
        "
     @question-added.window="Notify.notify('{{ __('cms.question_added') }}');"
     @question-removed.window="Notify.notify('{{ __('cms.question_deleted') }}')"
>
    <div class="flex w-full flex-col border-b border-secondary sticky top-0 z-[2]">
        <div class="py-2 px-6 flex w-full bg-white border-b border-secondary">
            <div class="flex items-center space-x-2.5">
                <x-button.back-round @click="hideQuestionBank();"/>
                <span class="bold text-lg cursor-default">{{ __('cms.Bestaande vraag toevoegen') }}</span>
            </div>

            <div class="flex ml-auto items-center space-x-2.5">
                <x-button.cta @click="hideQuestionBank();">
                    <span>{{ __('drawing-modal.Sluiten') }}</span>
                </x-button.cta>

                <x-button.slider wire:model="sliderButtonSelected" :disabled="$sliderButtonDisabled"
                        button-width="135px" :options="$sliderButtonOptions"></x-button.slider>

            </div>

        </div>
        <div class="flex w-full bg-lightGrey">
            <div class="w-full   mx-auto">
                <div class="flex w-full space-x-4 mx-8 max-w-max">
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
                        <div class="flex relative text-midgrey cursor-default"
                             title="{{ __('general.Later beschikbaar') }}">
                            {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 3">--}}
                            <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                                  :class="openTab === 3 ? 'primary' : '' ">{{ __('general.Nationaal') }}</span>
                            <span class="absolute w-full bottom-0" style="height: 3px"
                                  :class="openTab === 3 ? 'bg-primary' : 'bg-transparent' "></span>
                        </div>
                    </div>

                    <div>
                        <div class="flex relative text-midgrey cursor-default"
                             title="{{ __('general.Later beschikbaar') }}">
                            {{--                    <div class="flex relative hover:text-primary cursor-pointer" @click="openTab = 4">--}}
                            <span class="bold pt-[0.9375rem] pb-[0.8125rem]"
                                  :class="openTab === 4 ? 'primary' : '' ">{{ __('general.Examens') }}</span>
                            <span class="absolute w-full bottom-0" style="height: 3px"
                                  :class="openTab === 4 ? 'bg-primary' : 'bg-transparent' "></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="flex w-full main" x-show="bodyVisibility" x-cloak>
        <div class="w-full  mx-auto divide-y divide-secondary">
            <div class="mx-8"
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
                        Livewire.hook('message.processed', (message, component) => filterLoading = false);
                     "
                 @enable-loading-grid.window="filterLoading = true;"
            >
                {{-- Filters--}}
                <div class="flex flex-col pt-4 pb-2">
                    <div class="flex w-full my-2">
                        <div class="relative w-full">
                            <x-input.text class="w-full"
                                          placeholder="{{ __('cms.search_placeholder') }}"
                                          wire:model.debounce.300ms="filters.{{ $this->openTab }}.search"
                            />
                            <x-icon.search class="absolute right-0 -top-2"/>
                        </div>
                    </div>
                    <div class="flex w-full items-center">
                        <div class="flex flex-wrap w-full space-x-2 items-center" x-cloak>
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


                        @if($this->hasActiveFilters())
                            <x-button.text-button class="ml-auto text-base"
                                                  size="sm"
                                                  @click="$dispatch('enable-loading-grid');document.getElementById('questionbank-{{ $this->openTab }}-active-filters').innerHTML = '';"
                                                  wire:click="clearFilters('{{ $this->openTab }}')"
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

                    <div id="questionbank-{{ $this->openTab }}-active-filters"
                         x-data
                         wire:key="filters-container-{{ $this->openTab }}"
                         wire:ignore
                         class="flex flex-wrap gap-2 mt-2 relative"
                    >
                        {{--                        <a class="block absolute inset-0 bg-allred z-10 pointer-events-none" x-show="filterLoading"></a>--}}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex flex-col py-4" style="min-height: 500px">
                    <div class="flex">
                        <span class="note text-sm">{{ $this->resultCount }} resultaten </span>
                    </div>

                    <x-grid class="mt-4" x-show="filterLoading" x-cloak>
                        @foreach(range(1,6) as $value)
                            <x-grid.loading-card :delay="$value"/>
                        @endforeach
                    </x-grid>
                    <x-grid class="mt-4" x-show="!filterLoading" x-cloak>
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
                    </x-grid>
                </div>
            </div>
        </div>
    </div>

    <div id="groupdetail" wire:ignore.self>
        <div class=" mx-auto">
            @if($this->groupQuestionDetail != null)
                <x-partials.group-question-details :groupQuestion="$this->groupQuestionDetail"/>
            @endif
        </div>
    </div>
</div>