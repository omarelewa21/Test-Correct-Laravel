<div id="question-bank"
     class="flex flex-col relative w-full min-h-full bg-lightGrey border-t border-secondary overflow-auto overflow-x-hidden"
     x-data="questionBank(
        @entangle('openTab'),
        @entangle('inGroup'),
        @entangle('inTestBankContext')
    )"
     @question-added.window="Notify.notify('{{ __('cms.question_added') }}');"
     @question-removed.window="Notify.notify('{{ __('cms.question_deleted') }}')"
     group-container
     x-on:show-group-details="showGroupDetailsQb($event.detail.questionUuid, $event.detail.inTest );"
     x-on:close-group-details="closeGroupDetailQb()"
     x-on:add-question-to-test="addQuestionToTest($event.detail.button, $event.detail.questionUuid, $event.detail.showQuestionBankAddConfirmation)"
     wire:ignore.self
>
    <x-menu.tab.container>
        <x-menu.tab.allowed-content-sources :content-sources="$allowedTabs"
                                            menu="questionBankOpenTab"
        ></x-menu.tab.allowed-content-sources>
    </x-menu.tab.container>


    <div class="flex w-full main @if($inTestBankContext) max-w-screen-2xl mx-auto @endif" x-show="bodyVisibility" x-cloak>
        <div class="w-full  mx-auto z-0">
            <div class="mx-8 divide-y divide-secondary"
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
                <div class="flex flex-col py-4">
                    <div class="flex w-full my-2">
                        <div class="relative w-full">
                            <x-input.text class="w-full"
                                          placeholder="{{ __('cms.Search...') }}"
                                          wire:model.debounce.300ms="filters.search"
                            />
                            @if(!$inTestBankContext) <x-icon.search class="absolute right-0 -top-2"/> @endif
                        </div>
                        @if($inTestBankContext)
                            <x-button.slider class="pl-2"
                                :options="[false => __('general.tests'), true => __('general.questions')]"
                                :initialStatus="true"
                                wire:model="showQuestionBank"
                                buttonWidth="auto"
                            />
                        @endif
                    </div>
                    <div class="flex w-full items-center">
                        <div class="flex flex-wrap w-full space-x-2 items-center" x-cloak>
                            @if($this->isExternalContentTab())
                                <x-input.choices-select :multiple="true"
                                                        :options="$this->baseSubjects"
                                                        :withSearch="true"
                                                        placeholderText="{{ __('general.Categorie')}}"
                                                        wire:model="filters.base_subject_id"
                                                        wire:key="qb_base_subject_id_{{ $this->openTab }}"
                                                        filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                                />
                            @else
                                <x-input.choices-select :multiple="true"
                                                        :options="$this->subjects"
                                                        :withSearch="true"
                                                        placeholderText="{{ __('student.subject')}}"
                                                        wire:model="filters.subject_id"
                                                        wire:key="qb_subject_id_{{ $this->openTab }}"
                                                        filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                                />
                            @endif
                            <x-input.choices-select :multiple="true"
                                                    :options="$this->educationLevelYear"
                                                    :withSearch="true"
                                                    placeholderText="{{ __('general.Leerjaar')}}"
                                                    wire:model="filters.education_level_year"
                                                    wire:key="qb_education_level_year_{{ $this->openTab }}"
                                                    filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                            />
                            <x-input.choices-select :multiple="true"
                                                    :options="$this->educationLevel"
                                                    :withSearch="true"
                                                    placeholderText="{{ __('general.Niveau')}}"
                                                    wire:model="filters.education_level_id"
                                                    wire:key="qb_education_level_id_{{ $this->openTab }}"
                                                    filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                            />
                            <x-input.choices-select
                                    wire:key="qb_taxonomy_{{ $this->openTab }}"
                                    :multiple="true"
                                    :options="$this->taxonomies"
                                    :withSearch="true"
                                    :sortOptions="false"
                                    placeholderText="{{ __('cms.Taxonomie') }}"
                                    wire:model="filters.taxonomy"
                                    filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                            />
                            @if($this->hasAuthorFilter())
                                <x-input.choices-select :multiple="true"
                                                        :options="$this->authors"
                                                        :withSearch="true"
                                                        placeholderText="{{ __('general.Auteurs')}}"
                                                        wire:model="filters.author_id"
                                                        wire:key="qb_author_id_{{ $this->openTab }}"
                                                        filterContainer="questionbank-{{ $this->openTab }}-active-filters"
                                />
                            @endif
                        </div>

                        <x-button.text-button class="ml-auto text-base"
                                              size="sm"
                                              wire:click="clearFilters()"
                                              x-on:click="$dispatch('enable-loading-grid');clearFilterPillsFromElement($refs.questionbank);"
                                              :disabled="!$this->hasActiveFilters()"
                        >
                            <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                            <x-icon.close-small/>
                        </x-button.text-button>
                    </div>

                    <div id="questionbank-{{ $this->openTab }}-active-filters"
                         wire:ignore
                         wire:key="qb-filters-container-{{ $this->openTab }}"
                         x-data
                         class="flex flex-wrap gap-2 mt-2 relative"
                    >
                        {{--                        <a class="block absolute inset-0 bg-allred z-10 pointer-events-none" x-show="filterLoading"></a>--}}
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex flex-col py-4" style="min-height: 500px">
                    @if ($inTestBankContext)
                        <div class="flex justify-between">
                            <span class="note text-sm">{{ $this->resultCount }} @lang('cms.resultaten') </span>
                            @if(!auth()->user()->isValidExamCoordinator())
                                <div class="flex space-x-2.5">
                                    <x-button.cta class="px-4"
                                                wire:click="$emit('openModal', 'teacher.test-start-create-modal')"
                                                selid="create-new-test-button"
                                    >
                                        <x-icon.plus/>
                                        <span>{{ __('general.create test') }}</span>
                                    </x-button.cta>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex">
                            <span class="note text-sm">{{ $this->resultCount }} @lang('cms.resultaten') </span>
                        </div>
                    @endif

                    <x-grid class="mt-4" x-show="filterLoading" x-cloak>
                        @foreach(range(1,6) as $value)
                            <x-grid.loading-card :delay="$value" x-show="filterLoading"/>
                        @endforeach
                    </x-grid>
                    <x-grid class="mt-4" x-show="!filterLoading" x-cloak selid="question-bank-list">
                        @foreach($this->questions as $question)
                            <x-grid.question-card :question="$question" :inTest="$this->testContainsQuestion($question)"
                                                  context="question-bank"/>
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
                                @if(!$this->questions->count())
                                    @if($this->openTab === 'personal')
                                        {{ __('cms.no_personal_content_search_filters') }}
                                    @else
                                        {{ __('cms.no_public_content_search_filters') }}
                                    @endif
                                @else
                                    {{ __('cms.no_items_search_filter') }}
                                @endif
                        </span>
                        @endif
                    </x-grid>
                    @if(!$this->groupQuestionDetail)
                        <livewire:context-menu.question-card>
                    @endif
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