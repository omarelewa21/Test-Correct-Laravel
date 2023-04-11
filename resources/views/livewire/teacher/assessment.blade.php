<div id="assessment-page"
     class="min-h-full w-full assessment"
     x-data="assessment(@js($this->score), @js($this->currentQuestion?->score), @js((bool)$this->currentQuestion?->decimal_score), @js($this->drawerScoringDisabled), @js($this->updatePage))"
     x-cloak
     wire:key="page-@js($this->questionNavigationValue.$this->answerNavigationValue.$this->updatePage)"
     x-on:update-navigation.window="dispatchUpdateToNavigator($event.detail.navigator, $event.detail.updates)"
     x-on:slider-toggle-value-updated.window="toggleTicked($event.detail)"
     x-on:initial-toggle-tick.window="initialToggleTicked()"
>
    <x-partials.header.assessment :testName="$testName" />
    @if($this->headerCollapsed)
        <div class="flex min-h-[calc(100vh-var(--header-height))] relative">
            <div class="px-15 py-10 gap-6 flex flex-col flex-1 relative">
                <div class="progress-bar-container | fixed-sub-header-container h-4 bg-white/50 border-bluegrey border-y top-[calc(var(--header-height)+4px)]"
                >
                    <span @class(['progress-bar | sticky top-[100px] flex items-center justify-end absolute left-0 h-[calc(1rem-2px)] bg-primary pr-2', 'rounded-r-full' => $this->progress < 100])
                          style="width: @js($this->progress)%; transition: width 150ms ease-in"
                    >
                        <span @class([
                            'text-xs',
                            'text-sysbase absolute left-4' => $this->progress === 0,
                            'text-white' => $this->progress > 0,
                        ])>@js($this->progress)%</span>
                    </span>
                </div>

                {{-- Group section --}}
                <div class="flex flex-col text-xs">
                    <span>vraag: @js($this->currentQuestion->id)</span>
                    <span>antwoord: @js($this->currentAnswer->id)</span>
                    <span>testtake: @js($this->testTakeData->id)</span>
                    <span>subtype: @js($this->currentQuestion->subtype)</span>
                </div>
                @if($this->currentGroup)
                    <x-accordion.container :active-container-key="$this->groupPanel ? 'group' : ''"
                                           :wire:key="'group-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-accordion.block key="group"
                                           :emitWhenSet="true"
                                           :wire:key="'group-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                                           mode="transparent"
                        >
                            <x-slot:title>
                                <h4 class="flex items-center pr-4"
                                    selid="questiontitle"
                                >
                                    <span>@lang('question.Vraaggroep')</span>
                                    <span>:</span>
                                    <span x-cloak class="ml-2 text-left flex line-clamp-1"
                                          title="{!! $this->currentGroup->name !!}">
                                        {!! $this->currentGroup->name !!}
                                    </span>
                                    @if($this->currentGroup->isCarouselQuestion())
                                        <span class="ml-2 lowercase text-base"
                                              title="@lang('assessment.carousel_explainer')"
                                        >@lang('cms.carrousel')</span>
                                    @endif
                                </h4>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="flex flex-col gap-2"
                                     wire:key="group-block-{{  $this->currentGroup->uuid }}">
                                    <div class="flex flex-wrap">
                                        @foreach($this->currentGroup->attachments as $attachment)
                                            <x-attachment.badge-view :attachment="$attachment"
                                                                     :title="$attachment->title"
                                                                     :wire:key="'badge-'.$this->currentGroup->uuid"
                                                                     :question-id="$this->currentGroup->getKey()"
                                                                     :question-uuid="$this->currentGroup->uuid"
                                            />
                                        @endforeach
                                    </div>
                                    <div class="flex">
                                        {!! $this->currentGroup->converted_question_html !!}
                                    </div>
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                @endif

                {{-- Question section --}}
                @if($this->needsQuestionSection)
                    <x-accordion.container :active-container-key="$this->questionPanel ? 'question' : ''"
                                           :wire:key="'question-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-accordion.block key="question"
                                           :emitWhenSet="true"
                                           :wire:key="'question-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex">
                                    <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                        <span class="align-middle cursor-default">{{ $this->questionNavigationValue }}</span>
                                    </div>
                                    <div class="flex gap-4 items-center relative top-0.5">
                                        <h4 class="inline-flex"
                                            selid="questiontitle">
                                            <span>@lang('co-learning.question')</span>
                                            <span>:</span>
                                            <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                        </h4>
                                        <h7 class="inline-block">{{ $this->currentQuestion->score }} pt</h7>
                                    </div>
                                </div>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="flex flex-col gap-2"
                                     wire:key="question-block-{{  $this->currentQuestion->uuid }}">
                                    <div class="flex flex-wrap">
                                        @foreach($this->currentQuestion->attachments as $attachment)
                                            <x-attachment.badge-view :attachment="$attachment"
                                                                     :title="$attachment->title"
                                                                     :wire:key="'badge-'.$this->currentQuestion->uuid.$this->questionNavigationValue"
                                                                     :question-id="$this->currentQuestion->getKey()"
                                                                     :question-uuid="$this->currentQuestion->uuid"
                                            />
                                        @endforeach
                                    </div>

                                    <div class="max-w-full">
                                        @if($this->currentQuestion->isType('Completion'))
                                            {!! $this->currentQuestion->getDisplayableQuestionText()  !!}
                                        @else
                                            {!! $this->currentQuestion->converted_question_html !!}
                                        @endif
                                    </div>
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                @endif
                {{-- Answer section --}}
                @unless($this->currentQuestion->isType('infoscreen'))
                    <x-accordion.container :active-container-key="$this->answerPanel ? 'answer' : ''"
                                           :wire:key="'answer-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-accordion.block key="answer"
                                           :coloredBorderClass="'student'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-section-block-'.$this->questionNavigationValue.$this->answerNavigationValue"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex gap-4">
                                    <h4 class="flex items-center flex-wrap" selid="questiontitle">
                                        <span>@lang('co-learning.answer')</span>
                                        @if($this->assessmentContext['showStudentNames'])
                                            <span class="ml-2 truncate max-w-[170px]">{{ $this->currentAnswer->user->name_first }}</span>
                                            <span x-bind:class="{'ml-2': $el.previousElementSibling.offsetWidth < 170}">{{ $this->currentAnswer->user->shortLastname }}</span>
                                        @endif
                                        <span>:</span>
                                        <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                    </h4>
                                    <h7 class="inline-block min-w-fit">{{ $this->currentQuestion->score }} pt</h7>
                                </div>
                            </x-slot:title>
                            <x-slot:titleLeft>
                                <div class="ml-auto mr-6 relative top-0.5 flex gap-2 items-center">
                                    <span x-on:click.stop.prevent="">
                                        <x-tooltip class="w-[40px] h-[30px]" :icon-height="true" :icon-width="true">
                                            <x-slot:idleIcon>
                                                <span class="flex items-center" x-show="!tooltip" x-cloak>
                                                    <x-icon.profile />
                                                    <x-icon.questionmark-small/>
                                                </span>
                                            </x-slot:idleIcon>
                                            {{ $this->currentAnswer->user->nameFull }}
                                        </x-tooltip>
                                    </span>
                                    <x-dynamic-component :component="$this->currentAnswer->answeredStatus" />
                                </div>
                            </x-slot:titleLeft>
                            <x-slot:body>
                                <div class="student-answer | w-full"
                                     wire:key="student-answer-{{$this->currentQuestion->uuid.$this->currentAnswer->uuid}}"
                                >
                                    <x-dynamic-component
                                            :component="'answer.student.'. str($this->currentQuestion->type)->kebab()"
                                            :question="$this->currentQuestion"
                                            :answer="$this->currentAnswer"
                                            :editorId="'editor-'.$this->currentQuestion->uuid.$this->currentAnswer->uuid"
                                    />
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>

                    {{-- Answermodel section --}}
                    <x-accordion.container :active-container-key="$this->answerModelPanel ? 'answer-model' : ''"
                                           :wire:key="'answer-model-section-'.$this->questionNavigationValue.$this->answerNavigationValue"
                    >
                        <x-accordion.block key="answer-model"
                                           :coloredBorderClass="'primary'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-model-section-block'.$this->questionNavigationValue.$this->answerNavigationValue"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex">
                                    <h4 class="inline-block"
                                        selid="questiontitle">@lang('co-learning.answer_model')</h4>
                                </div>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="w-full" wire:key="answer-model-{{$this->currentQuestion->uuid}}">
                                    <x-dynamic-component
                                            :component="'answer.teacher.'. str($this->currentQuestion->type)->kebab()"
                                            :question="$this->currentQuestion"
                                            :editorId="'editor-'.$this->currentQuestion->uuid"
                                    />
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                @endif
            </div>

            <div class="drawer | right flex isolate overflow-hidden flex-shrink-0"
                 x-data="assessmentDrawer"
                 x-cloak
                 x-bind:class="{'collapsed': collapse}"
                 x-on:assessment-drawer-tab-update.window="tab($event.detail.tab)"
                 x-on:resize.window.throttle="handleResize"
            >
                <div class="collapse-toggle vertical white z-10 cursor-pointer"
                     @click="collapse = !collapse;"
                >
                    <button class="relative"
                            :class="{'rotate-svg-180 -left-px': collapse}"
                    >
                        <x-icon.chevron class="-top-px relative" />
                    </button>
                </div>

                <div class="flex flex-1 flex-col sticky top-[var(--header-height)]">
                    <div class="flex w-full justify-center gap-2 z-1"
                         style="box-shadow: 0 3px 8px 0 rgba(4, 31, 116, 0.2);">
                        <buttons
                                class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                                x-on:click="tab(1)"
                                x-bind:class="activeTab === 1 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                                title="@lang('assessment.scoren')"
                        >
                            <x-icon.review />
                        </buttons>
                        <buttons
                                class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                                x-on:click="tab(2)"
                                x-bind:class="activeTab === 2 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                                title="@lang('assessment.Feedback')"
                        >
                            <x-icon.feedback-text />
                        </buttons>
                        <buttons
                                @class([
                                    'flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors',
                                    'text-midgrey pointer-events-none' => !$this->showCoLearningScoreToggle
                                    ])
                                x-on:click="tab(3)"
                                x-bind:class="activeTab === 3 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                                title="@lang($this->showCoLearningScoreToggle ? 'co-learning.co_learning' : 'assessment.CO-Learning no results')"
                                @disabled(!$this->showCoLearningScoreToggle)
                        >
                            <x-icon.co-learning />
                        </buttons>
                    </div>
                    <div id="slide-container"
                         class="slide-container | flex h-full max-w-[var(--sidebar-width)] overflow-x-hidden overflow-y-auto"
                         wire:ignore.self
                         x-on:scroll="closeTooltips()"
                    >
                        <div class="slide-1 scoring | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                            <div class="flex-col w-full">
                                @if($this->currentGroup)
                                    <div class="mb-2">
                                        <div class="h-8 flex items-center">
                                            <h5 class="inline-flex line-clamp-1" title="{!! $this->currentGroup->name !!}">{!! $this->currentGroup->name !!}</h5>
                                        </div>
                                        <div class="h-[3px] rounded-lg w-full bg-sysbase"></div>
                                    </div>
                                @endif
                                <div class="question-indicator | items-center flex w-full">
                                    <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                        <span class="align-middle cursor-default">{{ $this->questionNavigationValue }}</span>
                                    </div>
                                    <div class="flex gap-4 items-center relative top-0.5 w-full">
                                        <h4 class="inline-flex"
                                            selid="questiontitle">
                                            <span>{{ $this->currentQuestion->type_name }}</span>
                                        </h4>
                                        <h7 class="ml-auto inline-block">{{ $this->currentQuestion->score }} pt</h7>
                                    </div>
                                </div>
                            </div>
                            @if($this->showCoLearningScoreToggle)
                                <div class="colearning-answers | flex w-full items-center justify-between"
                                     title="@lang('assessment.score_assigned'): @js($this->coLearningScoredValue)"
                                     x-cloak
                                >
                                    <x-input.toggle disabled checked />
                                    <span class="bold text-base">@lang('assessment.Score uit CO-Learning')</span>
                                    <x-tooltip>@lang('assessment.colearning_score_tooltip')</x-tooltip>
                                </div>
                                <div @class([
                                          'notification py-0 px-4 gap-6 flex items-center',
                                          'warning' => !$this->currentAnswerCoLearningRatingsHasNoDiscrepancy(),
                                          'info' => $this->currentAnswerCoLearningRatingsHasNoDiscrepancy(),
                                          ])
                                >
                                    <x-icon.co-learning />
                                    <span class="bold">@lang($this->currentAnswerCoLearningRatingsHasNoDiscrepancy() ? 'assessment.no_discrepancy' : 'assessment.discrepancy')</span>
                                </div>
                            @endif
                            @if($this->showAutomaticallyScoredToggle)
                                <div class="auto-assessed | flex w-full items-center justify-between cursor-default"
                                     title="@lang('assessment.score_assigned'): @js($this->automaticallyScoredValue)"
                                     x-cloak
                                >
                                    <x-input.toggle disabled checked />
                                    <span class="bold text-base">@lang('assessment.Automatisch nakijken')</span>
                                    <x-tooltip>@lang('assessment.closed_question_checked_tooltip')</x-tooltip>
                                </div>
                            @endif
                            @if($this->showScoreSlider)
                                <div class="score-slider | flex w-full"
                                     wire:key="score-slider-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                >
                                    <x-input.score-slider modelName="score"
                                                          :maxScore="$this->currentQuestion->score"
                                                          :score="$this->score"
                                                          :halfPoints="$this->currentQuestion->decimal_score"
                                                          mode="small"
                                                          :disabled="$this->drawerScoringDisabled"
                                                          :focus-input="true"
                                    />
                                </div>
                            @endif
                            @if($this->showFastScoring)
                                <div class="fast-scoring | flex flex-col w-full gap-2"
                                     wire:key="fast-scoring-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                     x-data="fastScoring(
                                     @js($this->fastScoringOptions->map->value),
                                     @js($this->score),
                                     @js($this->drawerScoringDisabled)
                                 )"
                                     x-on:slider-score-updated.window="updatedScore($event.detail.score)"
                                     x-bind:class="{'disabled': disabled}"
                                >
                                    <span class="flex ">@lang('assessment.snelscore_opties')</span>
                                    <div class="flex flex-col w-full gap-2">
                                        @foreach($this->fastScoringOptions as $key => $option)
                                            <div class="fast-option | flex flex-col w-full p-4 gap-2 border border-bluegrey rounded-md transition-all hover:border-primary hover:text-primary hover:bg-primary/5 cursor-pointer"
                                                 x-bind:class="{'active': fastOption === @js($key)}"
                                                 x-on:click="setOption(@js($key))"
                                                 wire:click="$set('score', @js($option['value']))"
                                            >
                                                <div class="borderdiv rounded-md"></div>
                                                <div class="flex justify-between items-center">
                                                    <div class="bold flex gap-2 items-center">
                                                        <span class="text-lg">{{ $option['points'] }}</span>
                                                        <span class="lowercase">@lang('cms.Punten')</span>
                                                    </div>
                                                    <span class="note text-sm">{{ $option['title'] }}</span>
                                                </div>
                                                <div class="flex">
                                                    <p>{{ $option['text'] }}</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="slide-2 feedback | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                            <div class="flex flex-col w-full gap-2">
                                <span class="flex ">@lang('assessment.Feedback toevoegen')</span>

                                <div class="flex w-full flex-col gap-2"
                                     wire:key="feedback-editor-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                >
                                    <x-input.rich-textarea type="assessment-feedback"
                                                           :editorId="'feedback-editor'. $this->questionNavigationValue.$this->answerNavigationValue"
                                                           wire:model.debounce.300ms="feedback"
                                                           :disabled="$this->currentQuestion->isSubType('writing')"

                                    />
                                    @if($this->currentQuestion->isSubType('writing'))
                                        <x-button.primary class="!p-0 justify-center"
                                                          wire:click="$emit('openModal', 'teacher.inline-feedback-modal', {answer: '{{  $this->currentAnswer->uuid }}' } );"
                                        >
                                            <span>@lang($this->hasFeedback ? 'assessment.Inline feedback wijzigen' : 'assessment.Inline feedback toevoegen')</span>
                                            <x-icon.chevron/>
                                        </x-button.primary>
                                        @if($this->hasFeedback)
                                            <x-button.text-button class="!p-0 justify-center"
                                                              wire:click="deleteFeedback"
                                            >
                                                <span>@lang('assessment.Inline feedback verwijderen')</span>
                                                <x-icon.chevron/>
                                            </x-button.text-button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="slide-3 co-learning | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4">
                            <div class="flex flex-col w-full gap-2">
                                <span class="flex ">@lang('assessment.CO-Learning scores')</span>
                                @if(!$this->currentAnswerCoLearningRatingsHasNoDiscrepancy())
                                    <div class="notification py-0 px-4 gap-6 flex items-center warning">
                                        <x-icon.co-learning />
                                        <span class="bold">@lang('assessment.discrepancy')</span>
                                    </div>
                                @endif
                                <div class="flex w-full flex-col gap-2">
                                    @if($this->showCoLearningScoreToggle)
                                        @foreach($this->coLearningRatings() as $rating )
                                            <div class="flex py-[7px] pl-3 pr-4 items-center border-l-4 border-l-student border border-bluegrey rounded-r-md rounded-l-sm">
                                                <div class="flex items-center justify-center w-[30px] min-w-[30px] h-[30px] border-bluegrey border bg-off-white overflow-hidden rounded-full">
                                                    <x-icon.profile class="scale-150 text-sysbase relative top-1" />
                                                </div>
                                                <span class="ml-2 truncate pr-2">{{ $rating->user->nameFull }}</span>
                                                <span class="ml-auto">@js($rating->displayRating)</span>
                                            </div>
                                        @endforeach
                                    @endif

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="nav-buttons | flex w-full justify-between items-center gap-2 px-6 h-[var(--header-height)] "
                         style="box-shadow: 0 -3px 8px 0 rgba(77, 87, 143, 0.3);"
                         wire:key="drawer-nav-buttons-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                    >
                        <x-button.text-button size="sm"
                                              x-on:click="previous"
                                              wire:target="previous,next"
                                              wire:loading.attr="disabled"
                                              wire:key="previous-button-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                              :disabled="$this->onBeginningOfAssessment()"
                        >
                            <x-icon.chevron class="rotate-180" />
                            <span>@lang('pagination.previous')</span>
                        </x-button.text-button>
                        <x-button.primary size="sm"
                                          x-on:click="next"
                                          wire:target="previous,next"
                                          wire:loading.attr="disabled"
                                          wire:key="next-button-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                          :disabled="$this->finalAnswerReached()"
                        >
                            <span>@lang('pagination.next')</span>
                            <x-icon.chevron />
                        </x-button.primary>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>