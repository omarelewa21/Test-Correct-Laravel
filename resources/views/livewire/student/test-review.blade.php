<main id="student-test-review"
      class="min-h-full w-full review"
>
    <header id="header"
            @class(['flex items-center py-2.5 px-6'])
    >
        <x-button.back-round wire:click="redirectBack()" title="@lang('test-take.Terug')" />

        <h6 class="flex ml-4">@lang('review.Inzien'): </h6>
        <h4 class="flex ml-2 mr-4 line-clamp-1" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>

        <div class="flex flex-col ml-auto items-end text-sm min-w-fit">
            <span class="inline-flex ">@lang('review.inzichtelijk tot'):</span>
            <span class="inline-flex ">{{ $this->reviewableUntil }}</span>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-var(--header-height))] relative">
        <div class="px-15 py-10 gap-6 flex flex-col flex-1 relative">
            <div class="flex flex-col">
                <span>vraag: @js($this->currentQuestion->id)</span>
                <span>antwoord: @js($this->currentAnswer->id)</span>
                <span>test take:@js($this->testTakeData->id)</span>
            </div>

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
                        <div class="question-indicator | items-center flex w-full">
                            <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                <span class="align-middle cursor-default">{{ $this->questionPosition }}</span>
                            </div>
                            <div class="flex gap-4 items-center relative top-0.5 w-full">
                                <h4 class="inline-flex"
                                    selid="questiontitle">
                                    <span>{{ $this->currentQuestion->type_name }}</span>
                                </h4>
                                <h7 class="ml-auto inline-block">{{ $this->currentQuestion->score }} pt</h7>
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
                                 wire:key="score-slider-{{  $this->questionPosition }}"
                            >
                                <x-input.score-slider modelName="score"
                                                      :maxScore="$this->currentQuestion->score"
                                                      :score="$this->score"
                                                      :halfPoints="$this->currentQuestion->decimal_score"
                                                      mode="small"
                                                      :disabled="true"
                                />
                            </div>
                        @endif
                    </div>
                    <div class="slide-2 feedback | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                        <div class="flex flex-col w-full gap-2">
                            <span class="flex ">@lang('assessment.Feedback toevoegen')</span>

                            <div class="flex w-full flex-col gap-2"
                                 wire:key="feedback-editor-{{  $this->questionPosition }}"
                            >
                                <x-input.rich-textarea type="assessment-feedback"
                                                       :editorId="'feedback-editor'. $this->questionPosition"
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
                     wire:key="drawer-nav-buttons-{{  $this->questionPosition }}"
                >
                    <x-button.text-button size="sm"
                                          x-on:click="previous"
                                          wire:target="previous,next"
                                          wire:loading.attr="disabled"
                                          wire:key="previous-button-{{  $this->questionPosition }}"
                                          :disabled="false"
                    >
                        <x-icon.chevron class="rotate-180" />
                        <span>@lang('pagination.previous')</span>
                    </x-button.text-button>
                    <x-button.primary size="sm"
                                      x-on:click="next"
                                      wire:target="previous,next"
                                      wire:loading.attr="disabled"
                                      wire:key="next-button-{{  $this->questionPosition }}"
                                      :disabled="$this->finalAnswerReached()"
                    >
                        <span>@lang('pagination.next')</span>
                        <x-icon.chevron />
                    </x-button.primary>
                </div>
            </div>
        </div>
    </div>
</main>