<main id="student-test-review"
      class="min-h-full w-full review"
>
    <header id="header" @class(['flex items-center py-2.5 px-6'])>
        <x-button.back-round wire:click="redirectBack()"
                             title="{{  __('test-take.Terug') }}"
                             backgroundClass="bg-white/20"
        />

        <h6 class="flex ml-4">@lang('review.Inzien'): </h6>
        <h4 class="flex ml-2 mr-4 line-clamp-1" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>

        <div class="flex flex-col ml-auto items-end text-sm min-w-fit">
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
                <div class="nav-container | fixed-sub-header-container h-20 bg-lightGrey border-bluegrey border-b top-[var(--header-height)]"
                >
                    <div class="flex w-full h-full px-15 items-center invisible overflow-hidden"
                         x-data="reviewNavigation(@js($this->questionPosition))"
                         x-bind:class="{'invisible': !initialized }"
                    >
                        <div class="slider-buttons left | flex relative pt-4 -top-px h-full z-10" x-show="showSlider">
                            <button class="inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                                    x-on:click="start()">
                                <x-icon.arrow-last />
                            </button>
                            <button class="inline-flex base rotate-svg-180 w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                                    x-on:click="left()">
                                <x-icon.chevron />
                            </button>
                        </div>
                        <div id="navscrollbar"
                             class="question-indicator gap-2 pt-4 h-full"
                             x-bind:class="{'overflow-x-auto px-3' : showSlider}"
                        >
                            @foreach($this->answers as $answer)
                                <div class="flex flex-col gap-1 relative">
                                    <div @class([
                                    'question-number | mt-px inline-flex rounded-full text-center justify-center items-center cursor-pointer hover:shadow-lg',
                                    'active' => (int)$this->questionPosition === $loop->iteration,
                                    'done' => $answer->done,
                                    'connector' => $answer->connector
                                ])
                                         wire:click="loadQuestion(@js($loop->iteration))"
                                         x-on:click="$dispatch('assessment-drawer-tab-update', {tab: 1})"
                                    >
                                        <span class="align-middle px-1.5">@js($loop->iteration)</span>
                                    </div>
                                    @if($answer->feedback->isNotEmpty())
                                        <x-icon.feedback-text class="inline-flex" />
                                    @endif
                                </div>
                            @endforeach
                        </div>
                        <div class="slider-buttons right | flex relative pt-4 -top-px h-full z-10"
                             x-show="showSlider">
                            <button class="inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                                    x-on:click="right()">
                                <x-icon.chevron />
                            </button>
                            <button class="inline-flex base w-8 h-8 rounded-full transition items-center justify-center transform focus:outline-none"
                                    x-on:click="end()">
                                <x-icon.arrow-last />
                            </button>
                        </div>
                    </div>
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
                            <div class="student-answer | w-full"
                                 wire:key="student-answer-{{$this->currentQuestion->uuid.$this->currentAnswer->uuid}}"
                            >
                                <x-dynamic-component
                                        :component="'answer.student.'. str($this->currentQuestion->type)->kebab()"
                                        :question="$this->currentQuestion"
                                        :answer="$this->currentAnswer"
                                        :disabledToggle="true"
                                        :editorId="'editor-'.$this->currentQuestion->uuid.$this->currentAnswer->uuid"
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
                @if($this->hasFeedback)
                    <div>
                        <x-button.text-button x-on:click="tab(2)" size="sm" class="text-base">
                            <x-icon.feedback-text />
                            <span>@lang('review.Bekijk feedback')</span>
                        </x-button.text-button>
                    </div>
                @endif
            </x-slot:slideOneContent>
            <x-slot:slideTwoContent>
                <span class="flex bold">@lang('review.Gegeven feedback')</span>

                <div class="flex w-full flex-col gap-2"
                     wire:key="feedback-editor-{{  $this->questionPosition }}"
                >
                    @if($this->hasFeedback)
                        <x-button.primary class="!p-0 justify-center"
                                          wire:click="$emit('openModal', 'teacher.inline-feedback-modal', {answer: '{{  $this->currentAnswer->uuid }}', disabled: true });"
                        >
                            <span>@lang('review.Bekijk feedback')</span>
                            <x-icon.chevron />
                        </x-button.primary>
                    @endif
                </div>
            </x-slot:slideTwoContent>

            <x-slot:slideThreeContent>
                <span class="flex ">@lang('assessment.CO-Learning scores')</span>
                @if(!$this->currentAnswerCoLearningRatingsHasNoDiscrepancy())
                    <div class="notification py-3 warning">
                        <div class="title">
                            <x-icon.exclamation />
                            <span>@lang('review.Er waren verschillen')</span>
                        </div>
                        <span class="body">@lang('review.co_learning_differences')</span>
                    </div>
                @endif
                <div class="flex w-full flex-col gap-2">
                    @if($this->showCoLearningScoreToggle)
                        @foreach($this->coLearningRatings() as $rating )
                            <div class="flex py-[7px] pl-3 pr-4 items-center border-l-4 border-l-student border border-bluegrey rounded-r-md rounded-l-sm">
                                <div class="flex items-center justify-center w-[30px] min-w-[30px] h-[30px] border-bluegrey border bg-off-white overflow-hidden rounded-full">
                                    <x-icon.profile class="scale-150 text-sysbase relative top-1" />
                                </div>
                                <span class="ml-2 truncate pr-2">Student {{ $loop->iteration }}</span>
                                <span class="ml-auto">@js($rating->displayRating)</span>
                            </div>
                        @endforeach
                    @endif

                </div>
            </x-slot:slideThreeContent>

            <x-slot:buttons>
                <x-button.text-button size="sm"
                                      x-on:click="previous"
                                      wire:target="previous,next"
                                      wire:loading.attr="disabled"
                                      wire:key="previous-button-{{  $this->questionPosition }}"
                                      :disabled="(int)$this->questionPosition === 1"
                >
                    <x-icon.chevron class="rotate-180" />
                    <span>@lang('pagination.previous')</span>
                </x-button.text-button>
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
    <x-notification />
    @livewire('livewire-ui-modal')
</main>