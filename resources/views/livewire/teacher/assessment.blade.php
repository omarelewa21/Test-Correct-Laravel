<div id="assessment-page"
     class="min-h-full w-full"
     x-data="assessment"
     x-on:update-navigation.window="dispatchUpdateToNavigator($event.detail.navigator, $event.detail.updates)"
>
    <x-partials.header.assessment :testName="$testName" />
    @if($this->headerCollapsed)
        <div class="flex min-h-[calc(100vh-var(--header-height))] relative">
            <div class="px-15 py-10 gap-6 flex flex-col flex-1">
                {{-- Group section --}}
                <div class="flex flex-col">
                    <span>vraag: @js($this->currentQuestion->id)</span>
                    <span>antwoord: @js($this->currentAnswer->id)</span>
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
                                <div class="question-indicator items-center flex">
                                    <div class="flex gap-4 items-center relative top-0.5">
                                        <h4 class="inline-flex items-center pr-4"
                                            selid="questiontitle">
                                            <span>@lang('question.Vraaggroep')</span>
                                            <span>:</span>
                                            <span class="ml-2 text-left">{{ $this->currentGroup->name }}</span>
                                            @if($this->currentGroup->isCarouselQuestion())
                                                <span class="ml-2 lowercase text-base">@lang('cms.carrousel')</span>
                                            @endif
                                        </h4>
                                    </div>
                                </div>
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
                                        {!! $this->currentQuestion->converted_question_html !!}
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
                                    <h4 class="flex items-center" selid="questiontitle">
                                        <span>@lang('co-learning.answer')</span>
                                        <span>:</span>
                                        <span class="ml-2">{{ $this->currentQuestion->type_name }}</span>
                                    </h4>
                                    <h7 class="inline-block">{{ $this->currentQuestion->score }} pt</h7>
                                </div>
                            </x-slot:title>
                            <x-slot:titleLeft>
                                <div class="ml-auto mr-6 relative top-0.5">
                                    <x-dynamic-component :component="$this->currentAnswer->answeredStatus" />
                                </div>
                            </x-slot:titleLeft>
                            <x-slot:body>
                                <div class="w-full"
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

            <div class="drawer right flex isolate overflow-hidden flex-shrink-0"
                 x-data="assessmentDrawer"
                 x-cloak
                 x-bind:class="{'collapsed': collapse}"
            >
                <div class="collapse-toggle vertical white z-10 cursor-pointer"
                     @click="collapse = !collapse "
                >
                    <button class="relative"
                            :class="{'rotate-svg-180 -left-px': collapse}"
                    >
                        <x-icon.chevron class="-top-px relative" />
                    </button>
                </div>

                <div class="flex flex-1 flex-col sticky top-[var(--header-height)]">
                    <div class="flex w-full justify-center gap-2 "
                         style="box-shadow: 0 3px 8px 0 rgba(4, 31, 116, 0.2);">
                        <buttons
                                class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                                x-on:click="tab(1)"
                                x-bind:class="{'primary border-primary': activeTab === 1}"
                        >
                            <x-icon.review />
                        </buttons>
                        <buttons
                                class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                                x-on:click="tab(2)"
                                x-bind:class="{'primary border-primary': activeTab === 2}"
                        >
                            <x-icon.feedback-text />
                        </buttons>
                        <buttons
                                class="flex h-[60px] px-2 cursor-pointer items-center border-b-3 border-transparent hover:text-primary transition-colors"
                                x-on:click="tab(3)"
                                x-bind:class="{'primary border-primary': activeTab === 3}"
                        >
                            <x-icon.co-learning />
                        </buttons>
                    </div>
                    <div id="slide-container" class="flex h-full max-w-[var(--sidebar-width)] overflow-hidden">
                        <div class="slide-1 p-6 flex-[1_0_100%] h-full w-[var(--sidebar-width)] space-y-4">
                            <div class="question-indicator items-center flex w-full">
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
                            @if($this->showCoLearningScoreToggle)
                                <div class="colearning-answers | flex w-full items-center justify-between">
                                    <x-input.toggle disabled checked />
                                    <span class="bold text-base">@lang('assessment.Score uit CO-Learning')</span>
                                    <x-tooltip>

                                    </x-tooltip>
                                </div>
                            @endif
                            @if($this->showAutomaticallyScoredToggle)
                                <div class="auto-assessed | flex w-full items-center justify-between">
                                    <x-input.toggle disabled checked />
                                    <span class="bold text-base">@lang('assessment.Automatisch nakijken')</span>
                                    <x-tooltip>

                                    </x-tooltip>
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
                                />
                            </div>
                            @endif
                            @if($this->showFastScoring)
                            <div class="fast-scoring | flex flex-col w-full gap-2"
                                 wire:key="fast-scoring-{{  $this->questionNavigationValue.$this->answerNavigationValue }}"
                                 x-data="fastScoring(@js($this->fastScoringOptions->map->value), @js($this->score))"
                                 x-on:slider-score-updated.window="updatedScore($event.detail.score)"
                            >
                                <span class="flex ">Snelscore opties</span>
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
                                                    <span class="">punten</span>
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
                        <div class="slide-2 p-6 flex-[1_0_100%] h-full w-[var(--sidebar-width)] space-y-4">
                            Content Tab 2
                        </div>
                        <div class="slide-3 p-6 flex-[1_0_100%] h-full w-[var(--sidebar-width)] space-y-4">
                            Content Tab 3
                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif
</div>