<div id="assessment-page"
     class="min-h-full w-full assessment"
     x-data="assessment(@js($this->getScoringData()))"
     x-cloak
     x-on:update-navigation.window="dispatchUpdateToNavigator($event.detail.navigator, $event.detail.updates)"
     x-on:update-scoring-data.window="updateScoringData($event.detail)"
     x-on:slider-toggle-value-updated.window="toggleTicked($event.detail)"
     wire:key="page-{{ $this->questionNavigationValue.'-'.$this->answerNavigationValue.$this->updatePage }}"
>

    <x-partials.header.assessment :testName="$testName" />
    @if($this->headerCollapsed)
        <div class="flex min-h-[calc(100vh-var(--header-height))] relative"
                 x-data="AnswerFeedback(
                @js('editor-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue),
                @js('feedback-editor-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue),
                @js(auth()->user()->uuid),
                @js($this->currentQuestion->type),
                @js(false),
                @js($this->hasFeedback)
             )"
             x-on:resize.window.debounce.50ms="repositionAnswerFeedbackIcons()"
        >
            <x-partials.evaluation.main-content :question="$this->currentQuestion"
                                                :group="$this->currentGroup"
                                                :unique-key="$this->questionNavigationValue.$this->answerNavigationValue.$this->headerCollapsed"
                                                :navigation-value="$this->questionNavigationValue"
                                                :group-panel="$this->groupPanel"
                                                :question-panel="$this->questionPanel"
                                                :answer-model-panel="$this->answerModelPanel"
                                                :show-correction-model="true"
                                                class="mt-20"
            >
                <x-slot:subHeader>
                    <div class="nav-container | fixed-sub-header-container h-20 bg-lightGrey border-bluegrey border-b top-[var(--header-height)] z-1">
                        <x-partials.necklace-navigation :position="$this->questionNavigationValue">
                            <x-slot:loopSlot>
                                @foreach($this->questions as $question)
                                    <div @class(['flex flex-col gap-1 items-center'])>
                                        <div @class([
                                            'question-number | relative mt-px inline-flex rounded-full text-center justify-center items-center cursor-default',
                                            'done'           => true,
                                            'system-rated'   => !$question->isDiscussionTypeOpen,
                                            'fully-rated'    => $question->isDiscussionTypeOpen && $question->doneAssessing,
                                            'active'         => (int)$this->questionNavigationValue === $loop->iteration,
                                            'cursor-pointer' => $question->navEnabled
                                        ])
                                             @if($question->navEnabled)
                                                 x-on:click="loadQuestion(@js($loop->iteration))"
                                             @else
                                                 title="{{ $this->getTitleTagForNavigation($question) }}"
                                                @endif
                                        >
                                            <span class="align-middle px-1.5">@js($loop->iteration)</span>
                                            @if($question->connector)
                                                <span class="connector"></span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </x-slot:loopSlot>
                        </x-partials.necklace-navigation>
                    </div>
                </x-slot:subHeader>
                <x-slot:answerBlock>
                    <x-accordion.container :active-container-key="$this->answerPanel ? 'answer' : ''"
                                           :wire:key="'answer-section-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue"
                    >
                        <x-accordion.block key="answer"
                                           :coloredBorderClass="'student'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-section-block-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex gap-4">
                                    <h4 class="flex items-center flex-wrap" selid="questiontitle">
                                        <span>@lang('co-learning.answer')</span>
                                        @if($this->assessmentContext['assessment_show_student_names'])
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
                                                    <x-icon.profile/>
                                                    <x-icon.i-letter/>
                                                </span>
                                            </x-slot:idleIcon>
                                            {{ $this->currentAnswer->user->nameFull }}
                                        </x-tooltip>
                                    </span>
                                    <x-dynamic-component :component="$this->currentAnswer->answeredStatus"/>
                                </div>
                            </x-slot:titleLeft>
                            <x-slot:body>
                                <div class="student-answer | w-full | questionContainer"
                                     wire:key="student-answer-{{$this->currentQuestion->uuid.'-'.$this->currentAnswer->uuid}}"
                                >
                                    <x-dynamic-component
                                            :component="'answer.student.'. str($this->currentQuestion->type)->kebab()"
                                            :question="$this->currentQuestion"
                                            :answer="$this->currentAnswer"
                                            :inAssessment="true"
                                            :editorId="'editor-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue"
                                            :disabled-toggle="true"
                                            :webSpellChecker="$this->webSpellCheckerEnabled"
                                            :commentMarkerStyles="$this->commentMarkerStyles"
                                            :enableComments="true"
                                    />
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                </x-slot:answerBlock>
            </x-partials.evaluation.main-content>

            <x-partials.evaluation.drawer :question="$this->currentQuestion"
                                          :group="$this->currentGroup"
                                          :navigation-value="$this->questionNavigationValue"
                                          :feedback-tab-disabled="false"
                                          :co-learning-enabled="$this->showCoLearningScoreToggle"
                                          :in-review="false"
                                          :unique-key="$this->questionNavigationValue.'-'.$this->answerNavigationValue"
            >
                <x-slot:slideOneContent>
                    @if($this->showCoLearningScoreToggle)
                        <div class="colearning-answers | flex w-full items-center justify-between"
                             title="@lang('assessment.score_assigned'): @js($this->coLearningScoredValue)"
                             x-cloak
                        >
                            <x-input.toggle disabled checked/>
                            <span class="bold text-base">@lang('assessment.Score uit CO-Learning')</span>
                            <x-tooltip>@lang('assessment.colearning_score_tooltip')</x-tooltip>
                        </div>
                        <div @class([
                                          'notification py-0 px-4 gap-6 flex items-center',
                                          'warning' => !$this->currentAnswerCoLearningRatingsHasNoDiscrepancy(),
                                          'info' => $this->currentAnswerCoLearningRatingsHasNoDiscrepancy(),
                                          ])
                        >
                            <x-icon.co-learning class="min-w-[1rem]" />
                            <span class="bold">@lang($this->getDiscrepancyTranslationKey())</span>
                        </div>
                    @endif
                    @if($this->showAutomaticallyScoredToggle)
                        <div class="auto-assessed | flex w-full items-center justify-between cursor-default"
                             title="@lang('assessment.score_assigned'): @js($this->automaticallyScoredValue)"
                             x-cloak
                        >
                            <x-input.toggle disabled checked/>
                            <span class="bold text-base">@lang('assessment.Automatisch nakijken')</span>
                            <x-tooltip>@lang('assessment.closed_question_checked_tooltip')</x-tooltip>
                        </div>
                    @endif
                    @if($this->showScoreSlider)
                        <div class="score-slider | flex w-full"
                             wire:key="score-slider-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                        >
                            <x-input.score-slider modelName="score"
                                                  mode="small"
                                                  :maxScore="$this->currentQuestion->score"
                                                  :score="$this->score"
                                                  :halfPoints="$this->currentQuestion->decimal_score"
                                                  :disabled="$this->drawerScoringDisabled"
                                                  :focus-input="true"
                            />
                        </div>
                    @endif
                    @if($this->showFastScoring)
                        <div class="fast-scoring | flex flex-col w-full gap-2"
                             wire:key="fast-scoring-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
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
                </x-slot:slideOneContent>

                <x-slot:slideTwoContent>
                    <div x-data="{}"
                         x-on:answer-feedback-focus-feedback-editor.window="toggleFeedbackAccordion('add-feedback', true)"
                         x-on:answer-feedback-show-comments.window="toggleFeedbackAccordion('given-feedback', true)"
                    >
                        @if($this->inlineFeedbackEnabled)
                        <div class="answer-feedback-add-comment">
                            <button class="flex bold border-t border-blue-grey py-2 justify-between items-center w-full group"
                                  :class="$store.answerFeedback.editingComment !== null ? 'text-midgrey' : ''"
                                  @click="toggleFeedbackAccordion('add-feedback')"

                            >
                                <span>@lang('assessment.Feedback toevoegen')</span>
                                <span class="w-6 h-6 rounded-full flex justify-center items-center transition -mr-0.5
                                                group-hover:bg-primary/5
                                                group-active:bg-primary/10
                                                group-focus:bg-primary/5 group-focus:text-primary group-focus:border group-focus:border-[color:rgba(0,77,245,0.15)]
                                    "
                                      :class="dropdownOpened === 'add-feedback' ? 'rotate-svg-90' : ''"
                                >
                                    <x-icon.chevron></x-icon.chevron>
                                </span>
                            </button>

                            <div class="flex w-full flex-col" x-show="dropdownOpened === 'add-feedback'"
                                 x-collapse
                                 wire:key="feedback-editor-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                            >
                                    <x-input.comment-color-picker
                                            commentThreadId="new-comment"
                                            uuid="new-comment"
                                            :useCkEditorView="true"
                                    ></x-input.comment-color-picker>


                                    <x-input.comment-emoji-picker
                                            commentThreadId="new-comment"
                                            uuid="new-comment"
                                            :new-comment="true"
                                            :useCkEditorView="true"
                                    ></x-input.comment-emoji-picker>

                                    <div class="comment-feedback-editor"
                                            x-on:click="$el.classList.add('editor-focussed')"
                                    >
                                        <label class="comment-feedback-editor-label flex"
                                               x-on:click="ClassicEditors['feedback-editor-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}'].focus()"
                                        >
                                            @lang('assessment.Feedback schrijven')
                                        </label>
                                        <x-input.rich-textarea type="create-answer-feedback"
                                                               :editorId="'feedback-editor-'. $this->questionNavigationValue.'-'.$this->answerNavigationValue"
                                        />
                                    </div>

                                    <div class="flex justify-end space-x-4 h-fit mt-2 mb-6"
                                         x-on:button-cancel-clicked="resetAddNewAnswerFeedback(true)"
                                         x-on:button-save-clicked="createCommentThread"
                                         wire:ignore
                                         id="saveNewFeedbackButtonWrapper"
                                         data-save-translation="@lang('general.save')"
                                         data-cancel-translation="@lang('modal.annuleren')"
                                    > {{-- filled by javascript with Ckeditor view components, cancel and save button --}}
                                    </div>
                            </div>
                        </div>

                        {{-- TODO: make exception for existing writing assignments? --}}
                        {{--@if($this->currentQuestion->isSubType('writing'))
                            <div class="border-2 border-dashed border-red-500">
                                @js('TODO: find out what to do with writing assignments')
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
                            </div>
                        @endif--}}
                        {{-- TODO: make exception for existing writing assignments? --}}

                            <div class="answer-feedback-given-comments | relative">
                                <button class="flex bold border-t border-blue-grey py-2 justify-between items-center w-full group"
                                        :class="{'text-midgrey': !hasFeedback}"
                                        x-init="dropdownOpened = hasFeedback ? dropdownOpened : 'add-feedback'"
                                        @click="toggleFeedbackAccordion('given-feedback')"
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
                                     wire:key="feedback-editor-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                                     x-data="{}"
                                     x-init=""
                                >

                                    <x-menu.context-menu.base context="answer-feedback">
                                        <div x-show="$store.answerFeedback.editingComment === null">
                                            <x-menu.context-menu.button @click="setEditingComment(uuid)">
                                                <x-slot:icon>
                                                    <x-icon.edit/>
                                                </x-slot:icon>
                                                <x-slot:text>@lang('cms.Wijzigen')</x-slot:text>
                                            </x-menu.context-menu.button>
                                        </div>

                                        <x-menu.context-menu.button @click="closeMenu(); deleteCommentThread(contextData?.threadId, uuid)">
                                            <x-slot:icon>
                                                <x-icon.trash/>
                                            </x-slot:icon>
                                            <x-slot:text>@lang('cms.Verwijderen')</x-slot:text>
                                        </x-menu.context-menu.button>

                                    </x-menu.context-menu.base>
                                    @foreach($answerFeedback as $comment)

                                        <x-partials.answer-feedback-card :comment="$comment"/>

                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="flex w-full flex-col gap-2">
                                <span>@lang('assessment.Feedback schrijven')</span>
                                <x-input.rich-textarea type="assessment-feedback"
                                                       :editorId="'feedback-editor-'. $this->questionNavigationValue.'-'.$this->answerNavigationValue"
                                                       wire:model.debounce.300ms="feedback"
                                                       :disabled="$this->currentQuestion->isSubType('writing')" {{-- todo find out what to do with writing assignment exceptions --}}
                                />
                            </div>
                        @endif
                    </div>
                </x-slot:slideTwoContent>

                <x-slot:slideThreeContent>
                    <span class="flex ">@lang('assessment.CO-Learning scores')</span>
                    @if(!$this->currentAnswerCoLearningRatingsHasNoDiscrepancy())
                        <div class="notification py-0 px-4 gap-6 flex items-center warning">
                            <x-icon.co-learning/>
                            <span class="bold">@lang('assessment.discrepancy')</span>
                        </div>
                    @endif
                    <div class="flex w-full flex-col gap-2">
                        @if($this->showCoLearningScoreToggle)
                            @foreach($this->coLearningRatings() as $rating )
                                <div class="flex py-[7px] pl-3 pr-4 items-center border-l-4 border-l-student border border-bluegrey rounded-r-md rounded-l-sm">
                                    <div class="flex items-center justify-center w-[30px] min-w-[30px] h-[30px] border-bluegrey border bg-off-white overflow-hidden rounded-full">
                                        <x-icon.profile class="scale-150 text-sysbase relative top-1"/>
                                    </div>
                                    <span class="ml-2 truncate pr-2">{{ $rating->user->nameFull }}</span>
                                    <span class="ml-auto">{{ $rating->displayRating }}</span>
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
                                          wire:key="previous-button-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                                          :disabled="$this->onBeginningOfAssessment()"
                                          selid="assessment-footer-previous"
                    >
                        <x-icon.chevron class="rotate-180"/>
                        <span>@lang('pagination.previous')</span>
                    </x-button.text-button>

                    @if($this->finalAnswerReached() && $this->assessedAllAnswers())
                        <x-button.cta size="sm"
                                      wire:click="redirectBack"
                                      wire:target="redirectBack,previous,next"
                                      wire:loading.attr="disabled"
                                      wire:key="next-button-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                                      selid="assessment-footer-finish"
                        >
                            <span>@lang('co-learning.finish')</span>
                        </x-button.cta>
                    @else
                        <x-button.primary size="sm"
                                          x-on:click="next"
                                          wire:target="previous,next"
                                          wire:loading.attr="disabled"
                                          wire:key="next-button-{{  $this->questionNavigationValue.'-'.$this->answerNavigationValue }}"
                                          :disabled="$this->finalAnswerReached()"
                                          selid="assessment-footer-next"
                        >
                            <span>@lang('pagination.next')</span>
                            <x-icon.chevron/>
                        </x-button.primary>
                    @endif
                </x-slot:buttons>
            </x-partials.evaluation.drawer>
        </div>
    @endif
</div>