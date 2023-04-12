<main id="student-test-review"
      class="min-h-full w-full review"
>
    <header id="header" @class(['flex items-center py-2.5 px-6'])>
        <x-button.back-round wire:click="redirectBack()"
                             title="@lang('test-take.Terug')"
                             backgroundClass="bg-white/20"
        />

        <h6 class="flex ml-4">@lang('review.Inzien'): </h6>
        <h4 class="flex ml-2 mr-4 line-clamp-1" title="{!!  clean($testName) !!}">{!!  clean($testName) !!}</h4>

        <div class="flex flex-col ml-auto items-end text-sm min-w-fit">
            <span class="inline-flex ">@lang('review.inzichtelijk tot'):</span>
            <span class="inline-flex ">{{ $this->reviewableUntil }}</span>
        </div>
    </header>

    <div class="flex min-h-[calc(100vh-var(--header-height))] relative">
        <div class="px-15 py-10 gap-6 flex flex-col flex-1 relative">
            {{-- Question necklace navigation  --}}
            <div class="nav-container | fixed-sub-header-container h-20 bg-lightGrey border-bluegrey border-b top-[var(--header-height)]"
            >
                <div class="flex w-full h-full px-15 items-center invisible overflow-hidden"
                     x-data="reviewNavigation(@js($this->questionPosition))"
                     x-bind:class="{'invisible': !initialized }"
                >
                    <div class="slider-buttons left | flex relative items-center h-full z-10" x-show="showSlider">
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
                         class="question-indicator gap-2"
                         x-bind:class="{'overflow-x-auto px-3' : showSlider}"
                    >
                        @foreach($this->answers as $answer)
                            <div class="flex flex-col items-center gap-1 relative">
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
                    <div class="slider-buttons right | flex relative items-center -top-px h-full z-10"
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

            {{-- Page content --}}
            <div class="flex flex-col mt-20">
                <span>vraag: @js($this->currentQuestion->id)</span>
                <span>antwoord: @js($this->currentAnswer->id)</span>
                <span>test take:@js($this->testTakeData->id)</span>
            </div>

            {{-- Group section --}}
            @if($this->currentGroup)
                <x-accordion.container :active-container-key="$this->groupPanel ? 'group' : ''"
                                       :wire:key="'group-section-'.$this->questionPosition"
                >
                    <x-accordion.block key="group"
                                       :emitWhenSet="true"
                                       :wire:key="'group-section-block-'.$this->questionPosition"
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
                                       :wire:key="'question-section-'.$this->questionPosition"
                >
                    <x-accordion.block key="question"
                                       :emitWhenSet="true"
                                       :wire:key="'question-section-block-'.$this->questionPosition"
                    >
                        <x-slot:title>
                            <div class="question-indicator items-center flex">
                                <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                    <span class="align-middle cursor-default">{{ $this->questionPosition }}</span>
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
                                                                 :wire:key="'badge-'.$this->currentQuestion->uuid.$this->questionPosition"
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

                {{-- Answermodel section --}}
                @if($this->showCorrectionModel)
                    <x-accordion.container :active-container-key="$this->answerModelPanel ? 'answer-model' : ''"
                                           :wire:key="'answer-model-section-'.$this->questionPosition"
                    >
                        <x-accordion.block key="answer-model"
                                           :coloredBorderClass="'primary'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-model-section-block'.$this->questionPosition"
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
            @endif

        </div>
        <div class="drawer | right flex isolate overflow-hidden flex-shrink-0"
             x-data="assessmentDrawer(true)"
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
                            @class([
                                'flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors',
                                'text-midgrey pointer-events-none' => !$this->hasFeedback,
                                ])
                            x-on:click="tab(2)"
                            x-bind:class="activeTab === 2 ? 'primary border-primary hover:border-primary' : 'hover:border-primary/25'"
                            title="@lang('assessment.Feedback')"
                            @disabled(!$this->hasFeedback)
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
                                        <h5 class="inline-flex line-clamp-1"
                                            title="{!! $this->currentGroup->name !!}">{!! $this->currentGroup->name !!}</h5>
                                    </div>
                                    <div class="h-[3px] rounded-lg w-full bg-sysbase"></div>
                                </div>
                            @endif
                            <div class="question-indicator | items-center flex w-full">
                                <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                    <span class="align-middle cursor-default">{{ $this->questionPosition }}</span>
                                </div>
                                <div class="flex justify-between items-center relative top-0.5 w-full">
                                    <h4 class="inline-flex line-clamp-1"
                                        selid="questiontitle"
                                        title="{{ $this->currentQuestion->type_name }}"
                                    >
                                        <span>{{ $this->currentQuestion->type_name }}</span>
                                    </h4>
                                    <div class="flex min-w-fit">
                                        <h7 class="ml-auto inline-block">{{  $this->score ?? '-' }}</h7>
                                        <span>/</span>
                                        <span class="body2">{{ $this->currentQuestion->score }} pt</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                    </div>
                    <div class="slide-2 feedback | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4 isolate">
                        <div class="flex flex-col w-full gap-2">
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
                        </div>
                    </div>
                    <div class="slide-3 co-learning | p-6 flex-[1_0_100%] h-fit min-h-full w-[var(--sidebar-width)] space-y-4">
                        <div class="flex flex-col w-full gap-2">
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
                </div>
            </div>
        </div>
    </div>

    <x-notification />
    @livewire('livewire-ui-modal')
</main>