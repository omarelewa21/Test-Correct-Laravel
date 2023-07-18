<div class="flex "
     x-data="AnswerFeedback(
                @js('ar-'. $this->answerRating->getKey()),
                @js('feedback-editor-'.$testTake->discussingQuestion->uuid),
                @js(auth()->user()->uuid),
                @js($this->currentQuestion->type),
                @js(false),
                @js($this->hasFeedback)
             )"
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
                    />
                </div>
            @endif
        </div>
    @endif
    <x-slot name="testName">{{ $testTake->test->name }}</x-slot>

    @if(!$finishCoLearningButtonEnabled && $waitForTeacherNotificationEnabled)
        <div class="fixed min-w-max right-1/2 translate-x-1/2 top-[93px] px-2 shadow border informational rounded leading-7 bold flex items-center">
            <x-icon.time-dispensation/>
            <span class="ml-2">{{ __('co-learning.wait_for_teacher') }}</span>
        </div>
    @endif
    <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
        @if(!$coLearningFinished)
            <div class="flex">
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
            </div>
            <div class="flex items-center space-x-4">
                <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span>
                <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>

                @if($previousAnswerAvailable)
                    <x-button.primary class="rotate-svg-180"
                                      wire:click="goToPreviousAnswerRating()"
                                      wire:loading.attr="disabled"
                    >
                        <x-icon.chevron />
                        <span>{{ __('co-learning.previous_answer') }}</span>
                    </x-button.primary>
                @elseif($nextAnswerAvailable)
                        <x-button.primary wire:click="goToNextAnswerRating()"
                                          wire:loading.attr="disabled"
                                          :disabled="!$this->enableNextQuestionButton"
                        >
                            <span>{{ __('co-learning.next_answer') }}</span>
                            <x-icon.chevron/>
                        </x-button.primary>
                @endif

                @if($this->atLastQuestion)
                    <x-button.cta wire:click="goToFinishedCoLearningPage"
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
{{--  TODO create drawer  --}}
    <x-partials.co-learning-drawer uniqueKey="question-{{$testTake->discussingQuestion->uuid}}" >
        <x-slot name="slideContent">
            <div x-data="{}"
                 x-on:answer-feedback-focus-feedback-editor.window="toggleFeedbackAccordion('add-feedback', true)"
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
                             wire:key="add-comment-container-{{$testTake->discussingQuestion->uuid}}"
                             wire:ignore
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
                                       x-on:click="ClassicEditors['feedback-editor-{{$testTake->discussingQuestion->uuid}}'].focus()"
                                >
                                    @lang('assessment.Feedback schrijven')
                                </label>
                                <x-input.rich-textarea type="create-answer-feedback"
                                                       :editorId="'feedback-editor-'. $testTake->discussingQuestion->uuid"
                                />
                            </div>

                            <div class="flex justify-end space-x-4 h-fit mt-2 mb-6"
                                 x-on:button-cancel-clicked="resetAddNewAnswerFeedback(true)"
                                 x-on:button-save-clicked="createCommentThread()"
                                 wire:key="add-comment-buttons-{{$testTake->discussingQuestion->uuid}}"
                                 id="saveNewFeedbackButtonWrapper"
                                 data-save-translation="@lang('general.save')"
                                 data-cancel-translation="@lang('modal.annuleren')"
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
                             wire:key="feedback-editor-{{$testTake->discussingQuestion->uuid}}"
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
            </div>
        </x-slot>
    </x-partials.co-learning-drawer>
</div>