@extends('layouts.test-take')

@section('gridData')
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.CO-Learning besproken vragen')</span>
        <h6>{{ $this->takenTestData['discussedQuestions'] }} / {{ $this->takenTestData['questionCount'] }}</h6>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.Nagekeken vragen')</span>
        <h6>{{ $this->takenTestData['assessedQuestions'] }} / {{ $this->takenTestData['questionsToAssess'] }}</h6>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('review.in te zien tot')</span>
        <span class="flex items-center gap-2">
            <x-button.text size="sm"
                           class="min-h-0 -mt-1"
                           wire:click="$emit('openModal', 'teacher.test-take.set-student-review-modal', {testTake: '{{ $this->testTakeUuid }}' });"
            >
                <x-icon.edit />
                <span>{{ $this->showResultsButtonText() }}</span>
            </x-button.text>
            <x-input.toggle :disabled="!$this->testTake->show_results"
                            wire:click="$toggle('reviewActive')"
                            :checked="$this->reviewActive"
            />
        </span>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.Resultaten gepubliceerd op')</span>
        <h6>{{ $this->testTake->results_published?->format('d-m-Y') ?? __('test-take.Nog niet gepubliceerd') }}</h6>
    </div>
@endsection

@section('cta')
    <div class="flex gap-2 justify-center">
        @if($this->testTakeHasNotFinishedDiscussing() || $this->testTakeIsDiscussedButNotCompletelyAssessed())
            <x-dynamic-component :component="'button.'. $this->getButtonType('CO-Learning')"
                                 wire:click="startCoLearning" class="px-4">
                <x-icon.co-learning />
                <span>@lang('co-learning.co_learning')</span>
            </x-dynamic-component>
            <x-dynamic-component :component="'button.'. $this->getButtonType('Assessment')"
                                 wire:click="startAssessment" class="px-4">
                <x-icon.review />
                <span>@lang('assessment.Nakijken')</span>
            </x-dynamic-component>
        @else
            @if($this->testTake->results_published)
                <div class="flex bg-cta/20 border-2 border-cta rounded-10 h-10 items-center px-4 text-ctamiddark gap-2 bold text-lg cursor-default">
                    <x-icon.checkmark />
                    <x-icon.grade />
                    <span>@lang('test-take.Resultaten gepubliceerd')</span>
                </div>
            @endif
            <x-button.cta :title="$this->publishButtonLabel()"
                          :disabled="!$this->canPublishResults()"
                          wire:click="publishResults"
            >
                <x-icon.grade />
                <span>{{ $this->publishButtonLabel() }}</span>
            </x-button.cta>
        @endif
    </div>
@endsection

@if($this->showResults())
    @section('studentNames')
        <div class="flex">
            <x-input.toggle-row-with-title wire:model="showStudentNames"
                                           container-class="!border-0"
                                           x-on:click="$dispatch('student-names-toggled', $el.checked)"
            >
                <x-icon.preview class="min-w-[20px]" />
                <span class="min-w-max">@lang('assessment.Studentnamen tonen')</span>
            </x-input.toggle-row-with-title>

        </div>
    @endsection
@endif

@section('action-buttons')
    @if($this->assessmentDone)
        @if($this->testTake->is_rtti_test_take)
            <x-button.icon class="order-3"
                           :title="__('teacher.Exporteer naar RTTI Online')"
                           wire:click="$emit('openModal', 'teacher.test-take.rtti-export-response-modal', {testTake: '{{ $this->testTake->uuid }}'})"
            >
                <x-icon.export />
            </x-button.icon>
        @else
            <x-button.icon class="order-3"
                           :title="__('teacher.RTTI Online export maken')"
                           type="link"
                           target="_blank"
                           :href="route('teacher.test-take.rtti-export-file', ['test_take' => $this->testTake->uuid])"
            >
                <x-icon.export />
            </x-button.icon>
        @endif
    @endif
    <x-button.icon class="order-5"
                   wire:click="$emit('openModal', 'teacher.test-plan-redo-modal', {testUuid: '{{ $this->testTake->test->uuid }}', testTakeUuid: '{{ $this->testTakeUuid }}' })"
                   :title="__('test-take.Inhaaltoets inplannen')"
    >
        <x-icon.redo-test />
    </x-button.icon>

    @if($this->testTakeHasNotFinishedDiscussing())
        <x-button.icon wire:click="startAssessment" class="order-5" :title="__('assessment.Start nakijken')">
            <x-icon.review />
        </x-button.icon>

        <x-button.cta wire:click="startCoLearning" class="px-4 order-1"
                      :title="__('co-learning.start_co_learning_session')">
            <x-icon.co-learning />
            <span>@lang('co-learning.co_learning')</span>
        </x-button.cta>
    @elseif($this->testTakeIsDiscussedButNotCompletelyAssessed())
        <x-button.icon wire:click="startCoLearning" class="order-5"
                       :title="__('co-learning.start_co_learning_session')">
            <x-icon.co-learning />
        </x-button.icon>

        <x-button.cta wire:click="startAssessment" class="px-4 order-1" :title="__('assessment.Start nakijken')">
            <x-icon.review />
            <span>@lang('assessment.Nakijken')</span>
        </x-button.cta>
    @else
        <x-button.icon wire:click="startAssessment" class="order-5"
                       :title="__('assessment.Start nakijken')">
            <x-icon.review />
        </x-button.icon>
        <x-button.icon wire:click="startCoLearning" class="order-5" :title="__('co-learning.start_co_learning_session')">
            <x-icon.co-learning />
        </x-button.icon>

        <x-button.icon class="order-3"
                       :title="__('test-take.Exporteer cijferlijst')"
                       x-on:click="

                        let windowReference = window.open();
                        windowReference.document.write(
                            PdfDownload.waitingScreenHtml('{{  __('test-pdf.pdf_download_wait_text') }}')
                        );

                        windowReference.location = '{{ route('teacher.pdf.grade-list', ['test_take' => $this->testTake->uuid]) }}'
                       "
        >
            <x-icon.grades-list />
        </x-button.icon>

        <x-button.cta class="order-1"
                      :title="$this->publishButtonLabel()"
                      :disabled="!$this->canPublishResults()"
                      wire:click="publishResults"
        >
            <x-icon.grade />
            <span>{{ $this->publishButtonLabel() }}</span>
        </x-button.cta>
    @endif
@endsection

@section('waitingRoom')
    <div @class(['flex flex-col gap-4', 'hidden' => !$this->showWaitingRoom])>
        <div class="flex flex-col gap-4">
            @if($this->showWaitingRoom)
                <h2>@lang('test-take.Wachtkamer')</h2>
                <div class="flex flex-col pt-4 pb-10 px-10 bg-white rounded-10 content-section relative"
                     x-data="{plannedTab: 'students'}"
                     x-cloak
                >
                    <x-menu.tab.container :withTileEvents="false" max-width-class="">
                        <x-menu.tab.item tab="students" menu="plannedTab" selid="test-take-overview-tab-taken"
                                         class="-ml-2">
                            @lang('test-take.Studenten')
                        </x-menu.tab.item>
                        <x-menu.tab.item tab="invigilators" menu="plannedTab" selid="test-take-overview-tab-norm">
                            @lang('student.invigilators')
                        </x-menu.tab.item>
                    </x-menu.tab.container>

                    <span class="absolute right-10 top-6 z-1">
                    <x-tooltip>@lang('test-take.waiting-room-tooltip')</x-tooltip>
                </span>

                    <div x-show="plannedTab === 'students'"
                         class="flex flex-col w-full pt-5"
                    >
                        <div class="flex w-full relative flex-wrap gap-2">
                            @if($this->initialized)
                                @forelse($this->participants as $participant)
                                    <div @class([
                                            'filter-pill px-4 gap-2 h-10 transition-opacity',
                                            'disabled' => !$participant->present,
                                            'enabled' => $participant->present
                                        ])
                                         wire:key="participant-{{ $participant->uuid }}-@js($participant->present)"
                                    >
                                        <span>{{ $participant->name }}</span>
                                    </div>
                                @empty
                                    <span>@lang('test-take.Geen studenten beschikbaar')</span>
                                @endforelse
                            @else
                                <div class="flex w-full h-full items-center justify-center">
                                    <x-icon.loading-large class="animate-spin" />
                                </div>
                            @endif
                        </div>
                    </div>
                    <div x-show="plannedTab === 'invigilators'"
                         class="flex flex-col w-full pt-5"
                    >
                        <div class="flex w-full relative flex-wrap gap-2">
                            @forelse($this->invigilatorUsers as $invigilatorUser)
                                <div class="filter-pill px-4 gap-2 h-10 enabled transition-opacity"
                                     wire:key="invigilator-{{ $invigilatorUser->uuid }}"
                                >
                                    <span>{{ $invigilatorUser->getFullNameWithAbbreviatedFirstName() }}</span>
                                </div>
                            @empty
                                <span>@lang('test-take.Geen surveillanten beschikbaar')</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <x-button.cta wire:click="startCoLearning"
                      class="justify-center"
                      size="md"
        >
            <x-icon.co-learning />
            <span>@lang('test-take.CO-Learning starten')</span>
        </x-button.cta>
    </div>
@endsection

@if($this->showStandardization())
    @section('norming')
        <div class="flex flex-col gap-4">
            <h2>@lang('test-take.Resultaten instellen')</h2>

            <div class="flex flex-col gap-8">
                <x-accordion.container :active-container-key="$this->needsToPublishResults() ? 'standardize' : ''">
                    <x-accordion.block key="standardize" :emitWhenSet="true">
                        <x-slot:title>
                            <h4>@lang('account.Becijferen en normeren')</h4>
                        </x-slot:title>

                        <x-slot:titleLeft>
                            <div class="ml-auto mr-2" x-on:click.stop.prevent="">
                                <x-tooltip>@lang('test-take.standardize_and_grading_tooltip')</x-tooltip>
                            </div>
                        </x-slot:titleLeft>

                        <x-slot:body>
                            <div class="flex flex-col w-full">
                                <div class="grid grid-cols-2 w-full gap-6 mb-4 mt-px">
                                    <div class="flex items-center gap-2 border-b border-bluegrey">
                                        <span class="bold">@lang('test-take.Normering'):</span>
                                        <x-input.select class="" wire:model="gradingStandard">
                                            @foreach($this->gradingStandards as $key => $language)
                                                <x-input.option :value="$key" :label="$language" />
                                            @endforeach
                                        </x-input.select>
                                        <x-input.text value="1"
                                                      class="min-w-[60px] w-[60px] text-center"
                                                      wire:model="gradingValue"
                                        />
                                        <div class="flex relative items-center">
                                            <x-input.text class="min-w-[80px] w-[80px] pr-8 pl-4"
                                                          :disabled="$this->gradingStandard !== 'cesuur'"
                                                          wire:model.debounce="cesuurPercentage"
                                                          type="number"
                                                          min="1"
                                                          max="100"
                                                          :error="$errors->has('cesuurPercentage')"
                                            />
                                            <span @class(["absolute right-4", 'note' => $this->gradingStandard !== 'cesuur'])>%</span>
                                        </div>

                                        <x-tooltip
                                                class="min-w-[22px]">@lang('test-take.standardize_tooltip')</x-tooltip>
                                    </div>

                                    <div class="flex items-center">
                                        {{-- __('test-take.show_grade_tooltip') --}}
                                        <x-input.toggle-row-with-title wire:model="showGradeToStudent">
                                            <x-icon.no-grade />
                                            <span>@lang('test-take.Cijfer tonen aan student')</span>
                                        </x-input.toggle-row-with-title>
                                    </div>
                                </div>

                                <div class="results-grid setup grid -mx-5 relative"
                                     x-data="{
                                        rowHover: null,
                                        shadow: null,
                                        usedSliders: [],
                                        clearUsedSliders() {
                                          this.usedSliders = [];

                                          this.$root.querySelectorAll('.score-slider-container').forEach(el => el.classList.add('untouched'))
                                        },
                                        }"
                                     x-init="
                                     shadow = $refs.shadowBox
                                     $watch('rowHover', value => {
                                        if(value !== null) {
                                            shadow.style.top = $root.querySelector(`[data-row='${value}'] .grid-item`)?.offsetTop + 'px'
                                        }
                                     })"
                                     wire:ignore.self
                                     x-on:clear-used-sliders.window="clearUsedSliders()"
                                >
                                    <div x-ref="shadowBox"
                                         x-bind:class="{'hidden': rowHover === null}"
                                         class="shadow-box "
                                         wire:ignore
                                    >
                                        <span></span>
                                    </div>

                                    <div class="bold pr-3 pl-5">
                                        <x-button.text
                                                class="group/button {{ $this->standardizeTabDirection === 'asc' ? 'rotate-svg-90' : 'rotate-svg-270' }}"
                                                wire:click.stop="changeStandardizeParticipantOrder"
                                                size="sm"
                                        >
                                            <span>@lang('test-take.Student')</span>
                                            <x-icon.chevron-small opacity="1"
                                                                  class="transform transition-all duration-100 group-hover/button:opacity-100 {{ is_null($this->standardizeTabDirection) ? 'opacity-0' : '' }}"
                                            />
                                        </x-button.text>
                                    </div>
                                    <div class="bold px-3">@lang('student.info')</div>
                                    <div class="bold px-3">@lang('test-take.Beoordeling')</div>
                                    <div class="bold px-3 text-right">@lang('test-take.Definitieve beoordeling')</div>
                                    <div class="bold px-3 pr-5">@lang('general.grade')</div>

                                    <div class="col-span-5 h-[3px] bg-sysbase mt-2 mx-5"></div>

                                    @foreach($this->sortParticipantResults($this->standardizeTabDirection) as $key => $participant)
                                        <div @class([
                                            "grid-row contents group/row cursor-default",
                                            "hover:text-primary hover:shadow-lg" => !$participant->testNotTaken,
                                            "disabled note" => $participant->testNotTaken,
                                        ])
                                             x-on:mouseover="rowHover = $el.dataset.row"
                                             x-on:mouseout="rowHover = null"
                                             data-row="{{ $loop->iteration }}"
                                             wire:key="participant-grading-row-{{ $participant->uuid }}-{{ $this->standardizeTabDirection }}"
                                        >
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-3 pl-5 col-start-1 h-15 rounded-l-10">{{ $participant->name }}</div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-3 ">
                                                <div class="flex items-center gap-2 truncate">
                                                    <div class="flex items-center gap-2 text-sysbase">
                                                    <span @class([
                                                            'flex items-center participant-popup-button',
                                                            'disabled' => $participant->testNotTaken
                                                        ])
                                                          x-on:click.prevent="if($el.dataset.open === 'false') $dispatch('open-participant-popup', {participant: @js($participant->uuid), element: $el})"
                                                          data-open="false"
                                                    >
                                                        <span data-closed>
                                                            <x-icon.profile />
                                                            <x-icon.i-letter />
                                                        </span>
                                                        <span data-open>
                                                            <x-icon.close-small />
                                                        </span>
                                                    </span>

                                                        @foreach($participant->contextIcons as $icon => $title)
                                                            <x-dynamic-component :component="'icon.'.$icon"
                                                                                 :title="$title"
                                                            />
                                                        @endforeach
                                                    </div>

                                                    <span class="truncate note text-sm group-hover/row:text-primary"
                                                          title="{{ $participant->invigilator_note }}">{{ $participant->invigilator_note }}</span>
                                                </div>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                                {{ $participant->definitiveRating ? str(round($participant->definitiveRating, 1))->replace('.', ',') : '-' }}
                                            </div>
                                            <div @class(["grid-item flex items-center group-hover/row:bg-offwhite px-3 w-full"])
                                                 x-on:change="usedSliders.push(@js($participant->uuid))"
                                            >
                                                <x-input.score-slider model-name="participantResults.{{ $key }}.rating"
                                                                      mode="large"
                                                                      :max-score="10"
                                                                      :min-score="1"
                                                                      :score="$participant->rating"
                                                                      :half-points="true"
                                                                      :title="false"
                                                                      :focus-input="false"
                                                                      wire:key="rating-{{ $participant->uuid.$participant->rating }}"
                                                                      class="justify-end"
                                                                      :use-indicator="true"
                                                                      data-slider-key="{{ $participant->uuid }}"
                                                />
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pl-3 pr-5 rounded-r-10 bold justify-end">
                                                <x-mark-badge :rating="$participant->rating" />
                                            </div>
                                        </div>
                                        <div class="h-px bg-bluegrey mx-5 col-span-5 col-start-1"></div>
                                    @endforeach
                                </div>
                                <div class="flex w-full bold pt-4 border-t-2 border-sysbase -mt-px z-1">
                                    <div class="flex w-1/3 justify-end">
                                        <div class="flex gap-4 items-center">
                                            <span>@lang('test-take.Hoogste cijfer')</span>
                                            <x-mark-badge :rating="$this->participantResults->max('rating')" />
                                        </div>
                                    </div>
                                    <div class="flex w-1/3 justify-end">
                                        <div class="flex gap-4 items-center">
                                            <span>@lang('test-take.Laagste cijfer')</span>
                                            <x-mark-badge :rating="$this->participantResults->min('rating')" />
                                        </div>
                                    </div>
                                    <div class="flex w-1/3 justify-end">
                                        <div class="flex gap-4 items-center">
                                            <span>@lang('test-take.Gemiddeld cijfer')</span>
                                            <x-mark-badge :rating="$this->participantResults->avg('rating')" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                <x-accordion.container>
                    <x-accordion.block key="test-questions" :emitWhenSet="true">
                        <x-slot:title>
                            <h4>@lang('test-take.Toetsvragen beheren')</h4>
                        </x-slot:title>

                        <x-slot:body>
                            <div class="flex flex-col w-full">
                                <div class="results-grid questions grid -mx-5 relative"
                                     x-data="{rowHover: null, shadow: null}"
                                     x-init="
                                     shadow = $refs.shadowBox
                                     $watch('rowHover', value => {
                                        if(value !== null) {
                                            shadow.style.top = $root.querySelector(`[data-row='${value}'] .grid-item`)?.offsetTop + 'px'
                                        }
                                     })"
                                     wire:ignore.self
                                >
                                    <div x-ref="shadowBox"
                                         x-bind:class="{'hidden': rowHover === null}"
                                         class="shadow-box "
                                         wire:ignore
                                    >
                                        <span></span>
                                    </div>

                                    <div class="bold pr-3 pl-5">#</div>
                                    <div class="bold px-3">@lang('test-take.Vraagtype')</div>
                                    <div class="bold px-3">@lang('cms.voorbeeld')</div>
                                    <div class="bold px-3 text-right">@lang('cms.p-waarde')</div>
                                    <div class="bold px-3">@lang('test-take.Gem score')</div>
                                    <div class="bold px-3">@lang('test-take.Max score')</div>
                                    <div class="bold px-3 pr-5">@lang('test-take.Overslaan')</div>

                                    <div class="col-span-7 h-[3px] bg-sysbase mt-2 mx-5"></div>

                                    @foreach($this->questionsOfTest as $question)
                                        <div @class([
                                             "grid-row contents group/row cursor-default",
                                             "hover:text-primary hover:shadow-lg" => !$question->isType('infoscreen'),
                                             "disabled note" => $question->isType('infoscreen'),
                                         ])
                                             x-on:mouseover="rowHover = $el.dataset.row"
                                             x-on:mouseout="rowHover = null"
                                             data-row="{{ $loop->iteration }}"
                                        >
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-3 pl-5 col-start-1 h-15 rounded-l-10">
                                                <span>{{ $question->order }}</span>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 ">
                                                <span>{{ $question->typeName }}</span>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 truncate justify-between gap-1.5">
                                                <span class="truncate"
                                                      title="{{ $question->title }}">{{ $question->title }}</span>
                                                <x-button.text
                                                        wire:click="$emit('openModal', 'teacher.question-cms-preview-modal', {uuid: '{{ $question->uuid }}' } );"
                                                        size="md"
                                                >
                                                    <x-icon.preview />
                                                </x-button.text>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                                <span>{{ $question->pValuePercentage ? $question->pValuePercentage . '%' : '-' }}</span>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                                <span>{{ $question->pValueAverage ?? '-' }}</span>
                                            </div>
                                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                                <span>{{ $question->pValueMaxScore ?? '-' }}</span>
                                            </div>
                                            <div @class([
                                                  "grid-item flex items-center group-hover/row:bg-offwhite pl-3 pr-5 rounded-r-10 justify-end",
                                                  "checkbox-disabled" => $question->isType('infoscreen')
                                              ])
                                            >
                                                <x-input.checkbox
                                                        :checked="$question->isType('infoscreen') || in_array($question->uuid, $this->questionsToIgnore)"
                                                        :disabled="$question->isType('infoscreen')"
                                                        wire:click="toggleQuestionToIgnore('{{ $question->uuid }}')"
                                                />
                                            </div>
                                        </div>

                                        @if(!$loop->last)
                                            <div class="h-px bg-bluegrey mx-5 col-span-7 col-start-1"></div>
                                        @endif
                                    @endforeach

                                    <div class="col-span-7 h-[2px] bg-sysbase mx-5 col-start-1"></div>
                                    <div class="contents cursor-default bold">
                                        <div class="grid-item flex items-center pt-6 pr-3 pl-5 col-start-1 "></div>
                                        <div class="grid-item flex items-center pt-6 px-3 "></div>
                                        <div class="grid-item flex items-center pt-6 px-3 justify-end">
                                            <span>@lang('test-take.Gecombineerd gemiddelde'):</span>
                                        </div>
                                        <div class="grid-item flex items-center pt-6 px-3 justify-end">
                                            <span>{{ round($this->questionsOfTest->map(fn($q) => $q->pValuePercentage)->avg(), 1) }}%</span>
                                        </div>
                                        <div class="grid-item flex items-center pt-6 px-3 justify-end">
                                            <span>{{ round($this->questionsOfTest->map(fn($q) => $q->pValueAverage)->avg(), 1) }}</span>
                                        </div>
                                        <div class="grid-item flex items-center pt-6 px-3 justify-end">
                                            <span>{{ round($this->questionsOfTest->map(fn($q) => $q->pValueMaxScore)->avg(), 1) }}</span>
                                        </div>
                                        <div class="grid-item flex items-center pt-6 pl-3 pr-5"></div>
                                    </div>
                                </div>

                                @if($errors->has('all_questions_ignored'))
                                    <div class="flex w-full justify-end mt-4">
                                        <div class="notification error stretched">
                                            <div class="body bold">{{  $errors->get('all_questions_ignored')[0] }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>

                <x-partials.test-take-student-grades-changed-notification :show="$this->participantGradesChanged" />

                <div class="flex flex-col justify-center gap-4">

                    <x-button.cta class="w-full"
                                  :title="$this->publishButtonLabel()"
                                  :disabled="!$this->canPublishResults()"
                                  size="md"
                                  wire:click="publishResults"
                    >
                        <x-icon.grade />
                        <span>{{ $this->publishButtonLabel() }}</span>
                    </x-button.cta>

                    @if($this->testTake->results_published)
                        <div class="flex self-center bg-cta/20 border-2 border-cta rounded-10 h-10 items-center px-4 text-ctamiddark gap-2 bold text-lg cursor-default">
                            <x-icon.checkmark />
                            <x-icon.grade />
                            <span>@lang('test-take.Resultaten gepubliceerd')</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endsection
@endif

@if($this->showResults())
    @section('results')
        <div class="flex flex-col gap-4">
            <h2>@lang('header.Resultaten')</h2>
            <div class="flex flex-col gap-8">
                <div class="flex flex-col pt-5 pb-10 px-10 bg-white rounded-10 content-section" x-cloak>
                    <h4>@lang('test-take.Resultaten overzicht')</h4>

                    <div class="divider mt-3 mb-2.5"></div>

                    <div class="results-grid overview grid -mx-5 relative"
                         x-data="{rowHover: null, shadow: null}"
                         x-init="
                         shadow = $refs.shadowBox
                         $watch('rowHover', value => {
                            if(value !== null) {
                                shadow.style.top = $root.querySelector(`[data-row='${value}'] .grid-item`)?.offsetTop + 'px'
                            }
                         })"
                         wire:ignore.self
                    >
                        <div x-ref="shadowBox"
                             x-bind:class="{'hidden': rowHover === null}"
                             class="shadow-box "
                             wire:ignore
                        >
                            <span></span>
                        </div>

                        <div class="bold pr-3 pl-5">
                            <x-button.text
                                    class="group/button {{ $this->resultsTabDirection === 'asc' ? 'rotate-svg-90' : 'rotate-svg-270' }}"
                                    wire:click.stop="changeResultsParticipantOrder"
                                    size="sm"
                            >
                                <span>@lang('test-take.Student')</span>
                                <x-icon.chevron-small opacity="1"
                                                      class="transform transition-all ease-in-out duration-100 group-hover/button:opacity-100  {{ is_null($this->resultsTabDirection) ? 'opacity-0' : '' }}"
                                />
                            </x-button.text>
                        </div>
                        <div class="bold px-3">@lang('student.info')</div>
                        <div class="bold px-3">@lang('test-take.Nagekeken')</div>
                        <div class="bold px-3">@lang('test-take.Score/Max')</div>
                        <div class="bold px-3">@lang('test-take.Discr.')</div>
                        <div class="bold pl-3 pr-5"></div>

                        <div class="col-span-6 h-[3px] bg-sysbase mt-2 mx-5"></div>

                        @foreach($this->sortParticipantResults($this->resultsTabDirection) as $participant)
                            <div @class([
                                    "grid-row contents group/row cursor-default",
                                    "hover:text-primary hover:shadow-lg" => !$participant->testNotTaken,
                                    "disabled note" => $participant->testNotTaken,
                                    ])
                                 x-on:mouseover="rowHover = $el.dataset.row"
                                 x-on:mouseout="rowHover = null"
                                 data-row="{{ $loop->iteration }}"
                                 wire:key="participant-results-row-{{ $participant->uuid }}-{{ $this->resultsTabDirection }}"
                            >
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-3 pl-5 col-start-1 h-15 rounded-l-10">{{ $participant->name }}</div>
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-3 ">
                                    <div class="flex items-center gap-2 truncate">
                                        <div class="flex items-center gap-2 text-sysbase">
                                            <span @class([
                                                    'flex items-center participant-popup-button',
                                                    'disabled' => $participant->testNotTaken
                                                ])
                                                  x-on:click.prevent="if($el.dataset.open === 'false') $dispatch('open-participant-popup', {participant: @js($participant->uuid), element: $el})"
                                                  data-open="false"
                                            >
                                                <span data-closed>
                                                    <x-icon.profile />
                                                    <x-icon.i-letter />
                                                </span>
                                                <span data-open>
                                                    <x-icon.close-small />
                                                </span>
                                            </span>

                                            @foreach($participant->contextIcons as $icon => $title)
                                                <x-dynamic-component :component="'icon.'.$icon"
                                                                     :title="$title"
                                                />
                                            @endforeach
                                        </div>

                                        <span class="truncate note text-sm group-hover/row:text-primary"
                                              title="{{ $participant->invigilator_note }}">{{ $participant->invigilator_note }}</span>
                                    </div>
                                </div>
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                    @if($participant->testNotTaken)
                                        <span>-/--</span>
                                    @else
                                        <span>{{ $participant->rated }}</span>/
                                        <span>{{ $participant->answers->count() }}</span>
                                    @endif
                                </div>
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                    @if($participant->testNotTaken)
                                        <span>-/--</span>
                                    @else
                                        <span>{{ $participant->score }}</span>/
                                        <span>{{ $this->takenTestData['maxScore'] }}</span>
                                    @endif
                                </div>
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite px-3 justify-end">
                                    @if($participant->testNotTaken)
                                        <span>--</span>
                                    @else
                                        <span>{{ $participant->discrepancies }}</span>
                                    @endif
                                </div>
                                <div class="grid-item flex items-center group-hover/row:bg-offwhite pl-3 pr-5 rounded-r-10">
                                    <div class="flex items-center gap-5 bold">
                                        <div class="flex items-center gap-2">
                                            <x-button.icon
                                                    wire:click="$emit('openModal', 'message-create-modal', {receiver: '{{ $participant->user->uuid }}'})"
                                                    :title="__('message.Stuur bericht')"
                                            >
                                                <x-icon.envelope class="w-4 h-4" />
                                            </x-button.icon>

                                            <x-button.icon wire:click="assessParticipant('{{ $participant->uuid }}')"
                                                           :title="__('test-take.Nakijken')"
                                                           :disabled="$participant->testNotTaken"
                                                           :color="$this->assessmentDone ? 'primary' : 'cta'"
                                            >
                                                <x-icon.review />
                                            </x-button.icon>
                                        </div>

                                        @if($participant->definitiveRating)
                                            <x-mark-badge :rating="$participant->definitiveRating" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="h-px bg-bluegrey mx-5 col-span-6 col-start-1"></div>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-col pt-5 pb-10 px-10 bg-white rounded-10 content-section" x-cloak>
                    <h4>@lang('test-take.Leerdoel analyse')</h4>

                    <div class="divider mt-3 mb-2.5"></div>

                    {{--Legend--}}
                    <div class="flex w-full items-center justify-center gap-4 bold">
                        <div>@lang('test-take.Legenda P-waarde'):</div>
                        <div class="flex gap-6">
                            <div class="flex items-center gap-1"><span
                                        class="flex w-4 h-4 rounded-[4px] bg-allred"></span><span>&lt; 55</span></div>
                            <div class="flex items-center gap-1"><span
                                        class="flex w-4 h-4 rounded-[4px] bg-student"></span><span>55 - 65</span>
                            </div>
                            <div class="flex items-center gap-1"><span
                                        class="flex w-4 h-4 rounded-[4px] bg-cta"></span><span>&gt; 65</span></div>
                        </div>
                    </div>

                    {{--Table--}}
                    <div class="h-px bg-bluegrey mt-3"></div>
                    <div class="flex flex-col w-full"
                         x-data="testTakeAttainmentAnalysis(@js($this->analysisQuestionValues))"
                         x-on:resize.window.throttle="fixPvalueContainerWidth"
                         x-on:student-names-toggled.window="resetAnalysis()"
                         wire:ignore
                    >
                        {{-- HEADER --}}
                        <div class="flex bold">
                            <div class="py-2 px-1.5 flex-1 flex justify-between">
                                <span class="capitalize">@lang('student.leerdoel')</span><span class="lowercase"># @lang('plan-test-take.Vragen'):</span>
                            </div>
                            <div class="pvalue-questions flex relative w-64 lg:w-96 xl:w-[600px]">
                                @foreach($this->analysisQuestionValues as $value)
                                    <div class="py-2 px-1.5"
                                         style="width: {{ 100 / count($this->analysisQuestionValues) }}%"
                                         data-questions="{{ $value }}">{{ $value }}</div>
                                @endforeach
                            </div>
                        </div>
                        <div class=" h-[2px] bg-sysbase"></div>
                        {{--Rows --}}
                        @foreach($this->attainments as $attainment)
                            <div class="flex w-full flex-col cursor-default">
                                <div class="flex w-full border-b border-bluegrey cursor-pointer group"
                                     x-on:click="toggleRow(@js($attainment->uuid));"
                                >
                                    <div class="flex flex-1 flex-col truncate">
                                        <div class="flex w-full items-center gap-3 py-2.5 bold">
                                            <span x-bind:class="{'rotate-svg-90': attainmentOpen.includes(@js($attainment->uuid))}"
                                                  x-bind:title="attainmentOpen.includes(@js($attainment->uuid)) ? $el.dataset.transCollapse : $el.dataset.transExpand"
                                                  @class(['flex items-center justify-center rounded-full min-w-[40px] w-10 h-10 transition group-hover:bg-primary/5 group-active:bg-primary/10 group-focus:bg-primary/5 group-focus:text-primary group-focus:border group-focus:border-[color:rgba(0,77,245,0.15)]'])
                                                  data-trans-collapse="@lang('general.inklappen')"
                                                  data-trans-expand="@lang('general.uitklappen')"
                                            >
                                                <svg @class(['transition group-hover:text-primary'])
                                                     width="9"
                                                     height="13"
                                                     xmlns="http://www.w3.org/2000/svg">
                                                    <path class="stroke-current" stroke-width="3" d="M1.5 1.5l5 5-5 5"
                                                          fill="none"
                                                          fill-rule="evenodd"
                                                          stroke-linecap="round" />
                                                </svg>
                                            </span>
                                            <span class="truncate">{{ collect([$attainment->code,$attainment->subcode,$attainment->subsubcode])->filter()->join('.') }} {{ $attainment->description }}</span>
                                        </div>
                                    </div>
                                    <div class="pvalue-container flex items-center">
                                        <span class="flex h-[26px] ml-2 rounded bold items-center px-2 text-white min-w-max"
                                              x-bind:style="styles(@js($attainment->p_value), @js($attainment->multiplier))"
                                              title="{{ $attainment->title }}"
                                        >
                                            <span style="text-shadow: 0 1px 2px rgba(4, 31, 116, 0.6);">P {{ $attainment->display_pvalue }}</span>
                                        </span>
                                    </div>
                                </div>

                                <div x-collapse
                                     x-show="attainmentOpen.includes(@js($attainment->uuid))"
                                     class="flex w-full flex-col "
                                >
                                    <template x-for="student in studentData[@js($attainment->uuid)]">
                                        <div class="flex w-full pl-5 "
                                             x-bind:class="{'border-b border-bluegrey': isLastStudentInRow(student, $el.dataset.attainment)}"
                                             data-attainment="{{ $attainment->uuid }}"
                                        >
                                            <div class="flex flex-1 py-2 truncate"
                                                 x-bind:class="{'border-b border-bluegrey': !isLastStudentInRow(student, $el.parentElement.dataset.attainment)}"
                                            >
                                                <span class="truncate"
                                                      x-text="student.name"
                                                      x-bind:title="student.name"></span>
                                            </div>
                                            <div class="pvalue-container flex items-center"
                                                 x-bind:class="{'border-b border-bluegrey': !isLastStudentInRow(student, $el.parentElement.dataset.attainment)}"
                                            >
                                                <span class="flex h-[26px] text-sm text-white ml-2 rounded bold items-center px-1 min-w-max"
                                                      x-bind:style="styles(student.p_value, student.multiplier)"
                                                      x-bind:title="student.title"
                                                >
                                                    <span style="text-shadow: 0 1px 2px rgba(4, 31, 116, 0.6);"
                                                          x-text="'P ' + student.display_pvalue"></span>
                                                </span>
                                            </div>
                                        </div>

                                    </template>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endsection
@endif

@section('grade-change-notification')
    <x-partials.test-take-student-grades-changed-notification :show="$this->participantGradesChanged" />
@endsection