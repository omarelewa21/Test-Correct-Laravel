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
            <x-button.text-button size="sm"
                                  class="min-h-0 -mt-1"
                                  wire:click="$emit('openModal', 'teacher.test-take.set-student-review-modal', {testTake: '{{ $this->testTakeUuid }}' });"
            >
                <x-icon.edit />
                <span>{{ $this->showResultsButtonText() }}</span>
            </x-button.text-button>
            <x-input.toggle :disabled="!$this->testTake->show_results"
                            wire:click="$toggle('reviewActive')"
                            :checked="$this->reviewActive"
            />
        </span>
    </div>
    <div class="flex flex-col gap-1">
        <span>@lang('test-take.Resultaten gepubliceerd op')</span>
        @if($this->testTake->results_published)
            <h6>{{ $this->testTake->results_published->format('d-m-Y') }}</h6>
        @else
            <h6>@lang('test-take.Nog niet gepubliceerd')</h6>
        @endif
    </div>
@endsection

@section('cta')
    <div class="flex gap-2 justify-center">
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
    </div>
@endsection

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_DISCUSSED)
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
    @if($this->testTake->is_rtti_test_take)
        <x-button.icon class="order-5">
            <x-icon.upload />
        </x-button.icon>
    @endif
    <x-button.icon class="order-5"
                   wire:click="$emit('openModal', 'teacher.test-plan-redo-modal', {testUuid: '{{ $this->testTake->test->uuid }}', testTakeUuid: '{{ $this->testTakeUuid }}' })">
        <x-icon.redo-test />
    </x-button.icon>

    @if(in_array($this->testTakeStatusId,[\tcCore\TestTakeStatus::STATUS_TAKEN,\tcCore\TestTakeStatus::STATUS_DISCUSSING]))
        <x-button.icon wire:click="startAssessment" class="order-5">
            <x-icon.review />
        </x-button.icon>

        <x-button.cta wire:click="startCoLearning" class="px-4 order-1">
            <x-icon.co-learning />
            <span>@lang('co-learning.co_learning')</span>
        </x-button.cta>
    @else
        <x-button.icon wire:click="startCoLearning" class="order-5">
            <x-icon.co-learning />
        </x-button.icon>

        <x-button.cta wire:click="startAssessment" class="px-4 order-1">
            <x-icon.review />
            <span>@lang('assessment.Nakijken')</span>
        </x-button.cta>
    @endif
@endsection

@section('waitingRoom')
    <div class="flex flex-col bg-white w-full">
        <div>
            <x-input.score-slider class=""
                                  model-name="rating"
                                  :max-score="7"
                                  :score="5"
                                  :half-points="true"
                                  :disabled="false"
                                  :co-learning="false"
                                  mode="small"
            />
        </div>
        <div>
            <x-input.score-slider class=""
                                  model-name="rating"
                                  :max-score="7"
                                  :score="5"
                                  :half-points="true"
                                  :disabled="false"
                                  :co-learning="false"
                                  mode="default"
            />
        </div>
        <div>
            <x-input.score-slider class=""
                                  model-name="rating"
                                  :max-score="10"
                                  :score="5"
                                  :half-points="true"
                                  :disabled="false"
                                  :co-learning="false"
                                  mode="default"
            />
        </div>
        <div>
            <x-input.score-slider class=""
                                  model-name="rating"
                                  :max-score="10"
                                  :score="5"
                                  :half-points="true"
                                  :disabled="false"
                                  :co-learning="false"
                                  mode="large"
                                  :title="false"
            />
        </div>
    </div>
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

@if($this->assessmentDone)
    @section('norming')
        <div class="flex flex-col gap-4">
            <h2>@lang('test-take.Resultaten instellen')</h2>
            <div class="flex flex-col pt-5 pb-10 px-10 bg-white rounded-10 content-section" x-cloak>
                <h4>@lang('account.Becijferen en normeren')</h4>

                <div class="divider mt-3 "></div>
                <div class="grid grid-cols-2 w-full gap-6 mb-4 mt-px">
                    <div class="flex items-center gap-2 border-b border-bluegrey">
                        <span class="bold">@lang('test-take.Normering'):</span>
                        <x-input.select class="">
                            @foreach($this->gradingStandards as $key => $language)
                                <x-input.option :value="$key" :label="$language" />
                            @endforeach
                        </x-input.select>
                        <x-input.text value="1" class="min-w-[60px] w-[60px] text-center"/>
                        <x-input.text value="100%" class="min-w-[80px] w-[80px] text-center"/>
                        <x-tooltip class="min-w-[22px]">Lekker tooltippen</x-tooltip>
                    </div>

                    <div class="flex items-center">
                        <x-input.toggle-row-with-title tool-tip="kaas is lekker">
                            <x-icon.no-grade/>
                            <span>@lang('test-take.Cijfer tonen aan student')</span>
                        </x-input.toggle-row-with-title>
                    </div>
                </div>

                <div class="results-grid setup grid -mx-5 relative"
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
                    <div x-ref="shadowBox" x-show="rowHover !== null" class="shadow-box "><span></span></div>

                    <div class="bold pr-1.5 pl-5">@lang('test-take.Student')</div>
                    <div class="bold px-1.5">@lang('student.info')</div>
                    <div class="bold px-1.5">@lang('test-take.Beoordeling')</div>
                    <div class="bold px-1.5 text-right">@lang('test-take.Definitieve beoordeling')</div>
                    <div class="bold px-1.5 pr-5">@lang('general.grade')</div>

                    <div class="col-span-5 h-[3px] bg-sysbase mt-2 mx-5"></div>

                    @foreach($this->participantResults as $key => $participant)
                        <div @class([
                                "grid-row contents group/row cursor-default",
                                "hover:text-primary hover:shadow-lg" => !$participant->testNotTaken,
                                "disabled note" => $participant->testNotTaken,
                                ])
                             x-on:mouseover="rowHover = $el.dataset.row"
                             x-on:mouseout="rowHover = null"
                             data-row="{{ $loop->iteration }}"
                        >
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-1.5 pl-5 col-start-1 h-15 rounded-l-10">{{ $participant->name }}</div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-1.5 ">
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
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                3
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
{{--                                <x-input.score-slider modelName="participantResults.{{ $key }}.score"--}}
{{--                                                      mode=""--}}
{{--                                                      :maxScore="10"--}}
{{--                                                      :score="0"--}}
{{--                                                      :halfPoints="true"--}}
{{--                                                      :disabled="$participant->testNotTaken"--}}
{{--                                                      title=""--}}
{{--                                />--}}
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pl-1.5 pr-5 rounded-r-10">
                                <x-mark-badge rating="10" />
                            </div>
                        </div>
                        <div class="h-px bg-bluegrey mx-5 col-span-5 col-start-1"></div>
                    @endforeach
                </div>
            </div>
        </div>
    @endsection
@endif

@if($this->testTakeStatusId >= \tcCore\TestTakeStatus::STATUS_DISCUSSED)
    @section('results')
        <div class="flex flex-col gap-4">
            <h2>@lang('header.Resultaten')</h2>
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
                    <div x-ref="shadowBox" x-show="rowHover !== null" class="shadow-box "><span></span></div>

                    <div class="bold pr-1.5 pl-5">@lang('test-take.Student')</div>
                    <div class="bold px-1.5">@lang('student.info')</div>
                    <div class="bold px-1.5">@lang('test-take.Nagekeken')</div>
                    <div class="bold px-1.5">@lang('test-take.Score/Max')</div>
                    <div class="bold px-1.5">@lang('test-take.Discr.')</div>
                    <div class="bold pl-1.5 pr-5"></div>

                    <div class="col-span-6 h-[3px] bg-sysbase mt-2 mx-5"></div>

                    @foreach($this->participantResults as $participant)
                        <div @class([
                                "grid-row contents group/row cursor-default",
                                "hover:text-primary hover:shadow-lg" => !$participant->testNotTaken,
                                "disabled note" => $participant->testNotTaken,
                                ])
                             x-on:mouseover="rowHover = $el.dataset.row"
                             x-on:mouseout="rowHover = null"
                             data-row="{{ $loop->iteration }}"
                        >
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-1.5 pl-5 col-start-1 h-15 rounded-l-10">{{ $participant->name }}</div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pr-1.5 ">
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
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>-/--</span>
                                @else
                                    <span>{{ $participant->rated }}</span>/
                                    <span>{{ $this->takenTestData['questionCount'] }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>-/--</span>
                                @else
                                    <span>{{ $participant->score }}</span>/
                                    <span>{{ $this->takenTestData['maxScore'] }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite px-1.5 justify-end">
                                @if($participant->testNotTaken)
                                    <span>--</span>
                                @else
                                    <span>{{ $participant->discrepancies }}</span>
                                @endif
                            </div>
                            <div class="grid-item flex items-center group-hover/row:bg-offwhite pl-1.5 pr-5 rounded-r-10">
                                <div class="flex items-center gap-5">
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
                                                       :color="$participant->rated === $this->takenTestData['questionCount'] ? 'primary' : 'cta'"
                                        >
                                            <x-icon.review />
                                        </x-button.icon>
                                    </div>
                                    <x-mark-badge rating="10" />
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
                        <div class="flex items-center gap-1"><span class="flex w-4 h-4 rounded-[4px] bg-student"></span><span>55 - 65</span>
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
    @endsection
@endif