<div class="flex w-full"
     @if($testTake->enable_comments_colearning && !$this->coLearningFinished)
     x-data="AnswerFeedback(
                @js('ar-'. $this->answerRating->getKey()),
                @js('feedback-editor-'. $this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber),
                @js(auth()->user()->uuid),
                @js($this->currentQuestion->type),
                @js(false),
                @js($this->hasFeedback)
             )"
     x-on:resize.window.debounce.50ms="repositionAnswerFeedbackIcons()"
     wire:key="ar-{{ $this->answerRating->getKey() }}-fe-{{$this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber}}"
     @endif
>
    <div id="co-learning-page"
         class="flex flex-col w-full pt-12 pb-12 items-stretch mx-8 xl:mx-28"
         @if($pollingFallbackActive) wire:poll.keep-alive.5000ms="updateHeartbeat()" @endif
         x-init="
         pusher = Echo.connector.pusher
         pusher.connection.bind('error', () => failureTest());
         pusher.connection.bind('connected', () => PusherConnectionSuccesful = true);
     "
         x-data="{
         PusherConnectionSuccesful: null,
         failureTest: (e) => {
            PusherConnectionSuccesful = false;
            $wire.set('pollingFallbackActive', true);
         },
     }"
         x-on:continue-navigation="Alpine.$data($el)[$event.detail.method]()"
    >

        @if($this->coLearningFinished)
            <div class="flex flex-col w-full" wire:key="co-learning-finished">
                <div class="w-full">
                    <livewire:co-learning.finished/>
                </div>
            </div>
        @else
            <div class="flex flex-col w-full" wire:key="q-{{$testTake->discussingQuestion->uuid}}">
                @if($this->noAnswerRatingAvailableForCurrentScreen)
                    <div class="w-full">
                        <livewire:co-learning.info-screen-question
                                :question="$this->testTake->discussingQuestion"
                                :questionNumber="$questionOrderNumber"
                                :answerNumber="$answerFollowUpNumber"
                                wire:key="q-{{$testTake->discussingQuestion->uuid}}"
                        />
                    </div>
                @else
                    <div class="w-full">
                        <livewire:is :component="$this->questionComponentName"
                                     :answerRatingId="$this->answerRating->getKey()"
                                     :questionNumber="$questionOrderNumber"
                                     :answerNumber="$answerFollowUpNumber"
                                     :wire:key="'ar-'. $this->answerRating->getKey()"
                                     :webSpellChecker="$this->testTake->enable_spellcheck_colearning"
                                     :inlineFeedbackEnabled="$this->testTake->enable_comments_colearning"
                                     :commentMarkerStyles="$this->commentMarkerStyles"
                                     :answerId="$this->answerRating->answer->getKey()"
                                     :answerFeedbackFilter="$this->answerFeedbackFilter"
                                     :testParticipantUuid="$this->testParticipant->uuid"
                        />
                    </div>

                    {{-- TODO START REFACTORING COLEARNING TO BLADE COMPONENT --}}
                    {{-- add openQuestion Answer --}}

                    <x-accordion.container
                            :active-container-key="true ? 'answer' : ''"
                            {{--:active-container-key="$this->answerPanel ? 'answer' : ''"--}}
                                           :wire:key="'answer-section-'.$this->questionOrderNumber.'-'.$this->answerFollowUpNumber"
                    >
                        <x-accordion.block key="answer"
                                           :coloredBorderClass="'student'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-section-block-'.$this->questionOrderNumber.'-'.$this->answerFollowUpNumber"
                        >
                            <x-slot:title>

                {{-- START TITLE --}}
                    <div class="question-title flex w-full items-center question-indicator mt-2 border-0 pb-0 mr-4">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $this->answered ? 'complete': 'incomplete' !!}">
                            <span class="align-middle cursor-default">{{ $this->questionOrderNumber }}</span>
                        </div>
                        @if($this->answerRating->answer->question->type !== 'InfoscreenQuestion')
                            <h5 class="inline-block ml-2">  {{__('co-learning.answer')}} {{ $this->answerFollowUpNumber }}:</h5>
                        @endif

                        <h2 class="inline-block ml-2 mr-6"
                            selid="questiontitle">{{ $this->answerRating->answer->question->type_name }}</h2>
                        @if($this->answerRating->answer->question->type !== 'InfoscreenQuestion')
                            <h7 class="inline-block">max. {{ $this->answerRating->answer->question->score }} pt</h7>
                        @endif
                        @if ($this->answered)
                            @if($this->isQuestionFullyAnswered())
                                <x-answered />
                            @else
                                <x-partly-answered />
                            @endif
                        @else
                            <x-not-answered />
                        @endif
                    </div>
                    {{-- END TITLE --}}
                            </x-slot:title>
                            <x-slot:body>



                    <div class="student-answer | w-full | questionContainer"
                         wire:key="student-answer-{{$this->testTake->discussingQuestion->uuid.'-'.$this->answerRating->answer->uuid}}-{{$this->answerFeedbackFilter}}"
                    >
                        {{-- TODO: Old component created an hash from updated at for the wire key for the comments plugin to stay in sync
                                wire:key="editor-ar-{{$answerRatingId}}-{{$this->updatedAtHash}}"
                        --}}
                        <x-dynamic-component
{{--                                :component="'answer.student.'. str($this->currentQuestion->type)->kebab()" --}}
                                :component="'answer.student.'. str($this->answerRating->answer->question->type)->kebab()"
                                :question="$this->testTake->discussingQuestion"
{{--                                :answer="$this->currentAnswer"--}}
                                :answer="$this->answerRating->answer"

{{--                                :editorId="'editor-'.$this->questionNavigationValue.'-'.$this->answerNavigationValue"--}}
                                :editorId="'editor-'.$this->answerRating->getKey()"

                                :inAssessment="true" {{-- completion question has two blade views and this toggles them --}}
                                :disabled-toggle="true" {{-- todo: disables some toggles in closed questions, find out what to do in colearning  --}}
                                {{-- webspellchecker plugin --}}  {{--:webSpellChecker="$this->webSpellCheckerEnabled"--}}
                                :webSpellChecker="$this->testTake->enable_spellcheck_colearning"
                                {{-- comments plugin --}}
                                :enableComments="$this->testTake->enable_comments_colearning"
                                :commentMarkerStyles="$this->commentMarkerStyles"
                                :answerFeedbackFilter="$this->answerFeedbackFilter"
                        />
                    </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                    {{-- TODO END REFACTORING COLEARNING TO BLADE COMPONENT --}}
                @endif
            </div>
        @endif
        <x-slot name="testName">{{  $testTake->test->name }}</x-slot>

        @if(!$finishCoLearningButtonEnabled && $waitForTeacherNotificationEnabled)
            <div class="fixed min-w-max right-1/2 translate-x-1/2 top-[93px] px-2 shadow border informational rounded leading-7 bold flex items-center">
                <x-icon.time-dispensation/>
                <span class="ml-2">{{ __('co-learning.wait_for_teacher') }}</span>
            </div>
        @endif
        <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
            @if(!$coLearningFinished)
                <div class="flex items-center space-x-4">
                    @if(!$this->noAnswerRatingAvailableForCurrentScreen)
                        <div class="flex content-center justify-between"
                             wire:key="ar-{{$this->answerRatingId}}"
                        >
                            <x-input.score-slider class=""
                                                  model-name="rating"
                                                  :max-score="$maxRating"
                                                  :score="$rating"
                                                  :half-points="$allowRatingWithHalfPoints"
                                                  :disabled="!$this->answerRating->answer->isAnswered"
                                                  :co-learning="true"
                            />
                        </div>
                    @endif
                    @if( $testTake->enable_comments_colearning && $this->currentQuestion->isType('OpenQuestion') )
                    <div class="flex content-center justify-between">
                        <x-button.secondary x-on:click.stop="$dispatch('answer-feedback-drawer-tab-update', {tab: 2} )">
                            <x-icon.feedback-text/>
                            <span>@lang('assessment.Feedback')</span>
                        </x-button.secondary>
                    </div>
                    @endif
                </div>
                <div class="flex items-center space-x-4">
                    <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span>
                    <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>

                    @if($previousAnswerAvailable)
                        <x-button.primary class="rotate-svg-180"
                                          x-on:click="await goToPreviousAnswerRating()"
                                          wire:loading.attr="disabled"
                        >
                            <x-icon.chevron/>
                            <span>{{ __('co-learning.previous_answer') }}</span>
                        </x-button.primary>
                    @elseif($nextAnswerAvailable)
                        <x-button.primary x-on:click="await goToNextAnswerRating()"
                                          wire:loading.attr="disabled"
                                          :disabled="!$this->enableNextQuestionButton"
                        >
                            <span>{{ __('co-learning.next_answer') }}</span>
                            <x-icon.chevron/>
                        </x-button.primary>
                    @endif

                    @if($this->atLastQuestion)
                        <x-button.cta x-on:click="await goToFinishedCoLearningPage"
                                      wire:loading.attr="disabled"
                                      :disabled="!$finishCoLearningButtonEnabled"
                        >
                            <span>{{ __('co-learning.finish') }}</span>
                        </x-button.cta>
                    @endif

                </div>
            @endif
        </footer>
    </div>
    @if($testTake->enable_comments_colearning && !$coLearningFinished && $testTake?->discussingQuestion->isType('OpenQuestion'))
    <x-partials.co-learning-drawer
            uniqueKey="ar-{{ $this->answerRating->getKey() }}-question-{{$testTake->discussingQuestion->uuid}}-{{ $this->answerFollowUpNumber }}-{{$this->getAnswerFeedbackUpdatedStateHash()}}">
        <x-slot name="slideContent">
            <div x-on:answer-feedback-focus-feedback-editor.window="toggleFeedbackAccordion('add-feedback', true)"
                 x-on:answer-feedback-show-comments.window="toggleFeedbackAccordion('given-feedback', true)"
            >
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
                         wire:ignore
                         x-init="createFocusableButtons(); $dispatch('reinitialize-editor-{{ 'feedback-editor-'. $this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber }}')"
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
                                   x-on:click="ClassicEditors['feedback-editor-{{ $this->questionFollowUpNumber }}-{{ $this->answerFollowUpNumber }}'].focus()"
                            >
                                @lang('assessment.Feedback schrijven')
                            </label>
                            <x-input.rich-textarea type="create-answer-feedback"
                                                   :editorId="'feedback-editor-'. $this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber"
                            />
                        </div>

                        <div class="flex justify-end space-x-4 h-fit mt-2 mb-6 items-center"
                             x-on:button-cancel-clicked="resetAddNewAnswerFeedback(true)"
                             x-on:button-save-clicked="createCommentThread()"
                             id="saveNewFeedbackButtonWrapper"
                             data-save-translation="@lang('general.save')"
                             data-cancel-translation="@lang('modal.annuleren')"
                             data-answer-editor-id="{{ 'ar-'. $this->answerRating->getKey() }}"
                             data-feedback-editor-id="{{ 'feedback-editor-'. $this->questionFollowUpNumber .'-'. $this->answerFollowUpNumber }}"
                        > {{-- filled by javascript with Ckeditor view components, cancel and save button --}}
                        </div>
                    </div>
                </div>

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

                            <x-menu.context-menu.button
                                    @click="closeMenu(); deleteCommentThread(contextData?.threadId, uuid)">
                                <x-slot:icon>
                                    <x-icon.trash/>
                                </x-slot:icon>
                                <x-slot:text>@lang('cms.Verwijderen')</x-slot:text>
                            </x-menu.context-menu.button>

                        </x-menu.context-menu.base>
                        @foreach($this->getVisibleAnswerFeedback() as $comment)

                            <x-partials.answer-feedback-card :comment="$comment"/>

                        @endforeach
                    </div>
                </div>
            </div>
        </x-slot>
    </x-partials.co-learning-drawer>
    @endif
</div>