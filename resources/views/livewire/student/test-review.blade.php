<main id="student-test-review"
      class="min-h-full w-full review"
      x-data="AnswerFeedback(
                @js('editor-'.$this->currentQuestion->uuid.$this->currentAnswer->uuid),
                @js(null),
                @js(auth()->user()->uuid),
                @js($this->currentQuestion->type),
                @js(true),
                @js($this->hasFeedback)
             )"
>
    <header id="header" @class(['flex items-center py-2.5 px-6'])>
        <x-button.back-round wire:click="redirectBack()"
                             title="{{  __('test-take.Terug') }}"
                             backgroundClass="bg-white/20"
        />

        <h6 class="flex ml-4">@lang('review.Inzien'): </h6>
        <h4 class="flex ml-2 mr-4 line-clamp-1" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>

        <div class="flex flex-col ml-auto items-end text-sm min-w-fit device-dependent-margin">
            <span class="inline-flex ">@lang('review.in te zien tot'):</span>
            <span class="inline-flex ">{{ $this->reviewableUntil }}</span>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-var(--header-height))] relative">
        <x-partials.evaluation.main-content :question="$this->currentQuestion"
                                            :group="$this->currentGroup"
                                            :unique-key="$this->questionPosition"
                                            :navigation-value="$this->questionPosition"
                                            :group-panel="$this->groupPanel"
                                            :question-panel="$this->questionPanel"
                                            :answer-model-panel="$this->answerModelPanel"
                                            :show-correction-model="$this->getShowCorrectionModelProperty()"
                                            class="mt-20"
        >
            <x-slot:subHeader>
                {{-- Question necklace navigation  --}}
                <div class="nav-container | fixed-sub-header-container h-20 bg-lightGrey border-bluegrey border-b top-[var(--header-height)]">
                    <x-partials.necklace-navigation :position="$this->questionPosition">
                        <x-slot:loopSlot>
                            @foreach($this->answers as $answer)
                                <div @class(['flex flex-col gap-1 items-center'])>
                                    <div @class([
                                            'question-number | relative mt-px inline-flex rounded-full text-center justify-center items-center cursor-pointer hover:shadow-lg',
                                            'active' => (int)$this->questionPosition === $loop->iteration,
                                            'done'   => $answer->done,
                                        ])
                                         x-on:click="loadQuestion(@js($loop->iteration))"
                                    >
                                        <span class="align-middle px-1.5">@js($loop->iteration)</span>
                                        @if($answer->connector)
                                            <span class="connector"></span>
                                        @endif
                                    </div>
                                    @if($answer->feedback->isNotEmpty())
                                        <x-icon.feedback-text class="inline-flex" />
                                    @endif
                                </div>
                            @endforeach
                        </x-slot:loopSlot>
                    </x-partials.necklace-navigation>
                </div>
            </x-slot:subHeader>

            <x-slot:answerBlock>
                <x-accordion.container :active-container-key="$this->answerPanel ? 'answer' : ''"
                                       :wire:key="'answer-section-'.$this->questionPosition"
                >
                    <x-accordion.block key="answer"
                                       :coloredBorderClass="'student'"
                                       :emitWhenSet="true"
                                       :wire:key="'answer-section-block-'.$this->questionPosition"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex gap-4">
                                <h4 class="flex items-center flex-wrap" selid="questiontitle">
                                    <span>@lang('co-learning.answer')</span>
                                    <span>:</span>
                                    <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                </h4>
                                <div class="flex min-w-fit text-base">
                                    <h7 class="inline-flex">{{  $this->score ?? '-' }}</h7>
                                    <span class="inline-flex font-normal">/</span>
                                    <span class="inline-flex body2 font-normal">{{ $this->currentQuestion->score }} pt</span>
                                </div>
                            </div>
                        </x-slot:title>
                        <x-slot:titleLeft>
                            <div class="ml-auto mr-6 relative top-0.5 flex gap-2 items-center">
                                <x-dynamic-component :component="$this->currentAnswer->answeredStatus" />
                            </div>
                        </x-slot:titleLeft>
                        <x-slot:body>
                            <div class="student-answer | w-full | questionContainer"
                                 wire:key="student-answer-{{$this->currentQuestion->uuid.$this->currentAnswer->uuid}}-{{$this->answerFeedbackFilter}}"
                            >
                                <x-dynamic-component
                                        :component="'answer.student.'. str($this->currentQuestion->type)->kebab()"
                                        :question="$this->currentQuestion"
                                        :answer="$this->currentAnswer"
                                        :disabledToggle="true"
                                        :editorId="'editor-'.$this->currentQuestion->uuid.$this->currentAnswer->uuid"
                                        :webSpellChecker="$this->currentQuestion->spell_check_available"
                                        :commentMarkerStyles="$this->commentMarkerStyles"
                                        :enableComments="true"
                                        :answerFeedbackFilter="$this->answerFeedbackFilter"
                                />
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container>
            </x-slot:answerBlock>
        </x-partials.evaluation.main-content>

        <x-partials.evaluation.drawer :question="$this->currentQuestion"
                                      :group="$this->currentGroup"
                                      :navigation-value="$this->questionPosition"
                                      :feedback-tab-disabled="!$this->hasFeedback"
                                      :co-learning-enabled="$this->showCoLearningScoreToggle"
                                      :in-review="true"
                                      :score="$this->score"
                                      :unique-key="$this->questionPosition"
        >
            <x-slot:slideOneContent>
                @unless($this->currentQuestion->isType('infoscreen'))
                <div class="score-slider | flex w-full relative"
                     wire:key="score-slider-{{  $this->questionPosition }}"
                >
                    <x-input.score-slider modelName="score"
                                          :maxScore="$this->currentQuestion->score"
                                          :score="$this->score"
                                          :halfPoints="$this->currentQuestion->decimal_score"
                                          mode="small"
                                          :disabled="false"
                                          :title="__('review.Jouw score')"
                                          :hideThumb="true"
                    >
                        <x-slot:tooltip>
                            <div class="ml-auto">
                                <x-tooltip>@lang('review.review_score_tooltip')</x-tooltip>
                            </div>
                        </x-slot:tooltip>
                    </x-input.score-slider>
                </div>
                @endif
                @if($this->hasFeedback)
                    <div>
                        <x-button.text x-on:click="tab(2, true)" size="sm" class="text-base">
                            <x-icon.feedback-text />
                            <span>@lang('review.Bekijk feedback')</span>
                        </x-button.text>
                    </div>
                @endif
            </x-slot:slideOneContent>
            <x-slot:slideTwoContent>
                <div class="answer-feedback-given-comments relative">
                    <button class="flex bold border-t border-blue-grey py-2 justify-between items-center w-full group"
                            :class="{'text-midgrey': !hasFeedback}"
                            x-init="dropdownOpened = 'given-feedback'"
                            @click="hasFeedback ? toggleFeedbackAccordion('given-feedback') : ''"
                    >
                        <span>@lang('assessment.Gegeven feedback')</span>
                        <span class="w-6 h-6 rounded-full flex justify-center items-center transition -mr-0.5
                                            group-hover:bg-primary/5
                                            group-active:bg-primary/10
                                            group-focus:bg-primary/5 group-focus:text-primary group-focus:border group-focus:border-[color:rgba(0,77,245,0.15)]
                                "
                              :class="dropdownOpened === 'given-feedback' ? 'rotate-svg-90' : ''"
                        >
                                    <x-icon.chevron></x-icon.chevron>
                                </span>
                    </button>

                    <div class="flex w-auto flex-col gap-2 given-feedback-container -mx-4"
                         x-show="dropdownOpened === 'given-feedback'"
                         x-collapse
                         wire:key="feedback-editor-{{  $this->questionPosition }}"
                         x-data="{}"
                         x-init=""
                    >
                        <div class="flex mx-auto "
                            x-on:multi-slider-toggle-value-updated.window="$wire.setAnswerFeedbackFilter($event.detail.value)"
                        >
                            <x-button.slider initial-status="all"
                                             buttonWidth="auto"
                                             :options="[ 'all' => __('assessment.all'), 'teacher' => __('auth.Docent'),'students' => __('test-take.Studenten')]"
                            />
                        </div>

                        @foreach($answerFeedback->filter->visible as $comment)

                            <x-partials.answer-feedback-card :comment="$comment"
                                                             :viewOnly="true"
                                                             :user-namefull="$anonymousStudentNames[$comment->user_id] ?? null"
                            />

                        @endforeach
                    </div>
                </div>

            </x-slot:slideTwoContent>

            <x-slot:slideThreeContent>
                <span class="flex ">@lang('assessment.CO-Learning scores')</span>
                @if(!$this->currentAnswerCoLearningRatingsHasNoDiscrepancy())
                    <x-notification-message type="warning">
                        <x-slot:title >
                            <x-icon.exclamation />
                            <span>@lang('review.Er waren verschillen')</span>
                        </x-slot:title>
                        @if($this->currentAnswerHasToggleDiscrepanciesInCoLearningRatings())
                            <x-slot:message>
                                <span>
                                     @lang('assessment.toggle_discrepancy')
                                </span>
                            </x-slot:message>
                        @else
                            <x-slot:message>
                                <span>
                                     @lang('review.co_learning_differences')
                                </span>
                            </x-slot:message>
                        @endif
                    </x-notification-message>
                @endif
                <div class="flex w-full flex-col gap-2">
                    @if($this->showCoLearningScoreToggle)
                        @foreach($this->coLearningRatings() as $rating )
                            <div class="flex py-[7px] pl-3 pr-4 items-center border-l-4 border-l-student border border-bluegrey rounded-r-md rounded-l-sm">
                                <div class="flex items-center justify-center w-[30px] min-w-[30px] h-[30px] border-bluegrey border bg-off-white overflow-hidden rounded-full">
                                    <x-icon.profile class="scale-150 text-sysbase relative top-1" />
                                </div>
                                <span class="ml-2 truncate pr-2">Student {{ $loop->iteration }}</span>
                                <span class="ml-auto">{{ $rating->displayRating }}</span>
                            </div>
                        @endforeach
                    @endif

                </div>
            </x-slot:slideThreeContent>

            <x-slot:buttons>
                <x-button.text size="sm"
                                      x-on:click="previous"
                                      wire:target="previous,next"
                                      wire:loading.attr="disabled"
                                      wire:key="previous-button-{{  $this->questionPosition }}"
                                      :disabled="(int)$this->questionPosition === 1"
                >
                    <x-icon.chevron class="rotate-180" />
                    <span>@lang('pagination.previous')</span>
                </x-button.text>
                @if($this->finalAnswerReached())
                    <x-button.cta size="sm"
                                  wire:click="redirectBack"
                                  wire:target="previous,next"
                                  wire:loading.attr="disabled"
                                  wire:key="next-button-{{  $this->questionPosition }}"
                    >
                        <span>@lang('review.finish')</span>
                        <x-icon.checkmark />
                    </x-button.cta>
                @else
                    <x-button.primary size="sm"
                                      x-on:click="next"
                                      wire:target="previous,next"
                                      wire:loading.attr="disabled"
                                      wire:key="next-button-{{  $this->questionPosition }}"
                    >
                        <span>@lang('pagination.next')</span>
                        <x-icon.chevron />
                    </x-button.primary>
                @endif
            </x-slot:buttons>

        </x-partials.evaluation.drawer>
    </div>
</main>