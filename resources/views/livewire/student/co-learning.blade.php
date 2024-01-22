<div class="flex w-full"
     @if($testTake->enable_comments_colearning && !$this->coLearningFinished)
         x-data="AnswerFeedback(
                @js('ar-'. $this->answerRating->getKey()),
                @js('feedback-editor-'. $this->questionOrderNumber .'-'. $this->answerFollowUpNumber),
                @js(auth()->user()->uuid),
                @js($this->currentQuestion->type),
                @js(false),
                @js($this->hasFeedback)
             )"
     x-on:resize.window.debounce.50ms="repositionAnswerFeedbackIcons()"
     wire:key="ar-{{ $this->answerRating->getKey() }}-fe-{{$this->questionOrderNumber .'-'. $this->answerFollowUpNumber}}"
     @endif
     x-on:slider-toggle-value-updated.window="toggleTicked($event.detail)"
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
            <div class="flex flex-col w-full gap-6" wire:key="q-{{$this->getDiscussingQuestion()->uuid}}">

                @if($this->testTake->enable_question_text_colearning)
                    {{-- start question text --}}

                    @if($group)
                        {{-- start group question container --}}
                        <x-accordion.container :active-container-key="true ? 'group' : ''"
                                               :wire:key="'group-section-'. $uniqueKey"
                        >
                            <x-accordion.block key="group"
                                               :emitWhenSet="true"
                                               :wire:key="'group-section-block-'. $uniqueKey"
                                               mode="transparent"
                            >
                                <x-slot:title>
                                    <h4 class="flex items-center pr-4"
                                        selid="questiontitle"
                                    >
                                        <span>@lang('question.Vraaggroep')</span>
                                        <span>:</span>
                                        <span x-cloak class="ml-2 text-left flex line-clamp-1"
                                              title="{!! $group->name !!}">
                                            {!! $group->name !!}
                                        </span>
                                        @if($group->isCarouselQuestion())
                                            <span class="ml-2 lowercase text-base"
                                                  title="@lang('assessment.carousel_explainer')"
                                            >@lang('cms.carrousel')</span>
                                        @endif
                                    </h4>
                                </x-slot:title>
                                <x-slot:body>
                                    <div class="flex flex-col gap-2"
                                         wire:key="group-block-{{  $group->uuid }}">
                                        <div class="flex flex-wrap">
                                            @foreach($group->attachments as $attachment)
                                                <x-attachment.badge-view :attachment="$attachment"
                                                                         :title="$attachment->title"
                                                                         :wire:key="'badge-'.$group->uuid"
                                                                         :question-id="$group->getKey()"
                                                                         :question-uuid="$group->uuid"
                                                />
                                            @endforeach
                                        </div>
                                        <div class="">
                                            {!! $group->converted_question_html !!}
                                        </div>
                                    </div>
                                </x-slot:body>
                            </x-accordion.block>
                        </x-accordion.container>
                        {{-- end group question container --}}
                    @endif
                    {{-- start question container --}}
                    <x-accordion.container :active-container-key="true ? 'question' : ''"
                                           :wire:key="'question-section-'. $uniqueKey"
                    >
                        <x-accordion.block key="question"
                                           :emitWhenSet="true"
                                           :wire:key="'question-section-block-'. $uniqueKey"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex">
                                    <div class="inline-flex question-number rounded-full text-center justify-center items-center">
                                        <span class="align-middle cursor-default">{{ $this->questionOrderAsInTestNumber }}</span>
                                    </div>
                                    <div class="flex gap-4 items-center relative top-0.5">
                                        <h4 class="inline-flex"
                                            selid="questiontitle">
                                            <span>@lang('co-learning.question')</span>
                                            <span>:</span>
                                            <span class="ml-2">{{ $this->getDiscussingQuestion()->type_name }}</span>
                                        </h4>
                                        <h7 class="inline-block">{{ $this->getDiscussingQuestion()->score }} pt</h7>
                                    </div>
                                </div>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="flex flex-col gap-2 questionContainer w-full"
                                     wire:key="question-block-{{  $this->getDiscussingQuestion()->uuid }}">
                                    <div class="flex flex-wrap" wire:key="attachment-container-{{ $uniqueKey }}">
                                        @foreach($this->getDiscussingQuestion()->attachments as $attachment)
                                            <x-attachment.badge-view :attachment="$attachment"
                                                                     :title="$attachment->title"
                                                                     :wire:key="'badge-'.$this->getDiscussingQuestion()->uuid. $uniqueKey"
                                                                     :question-id="$this->getDiscussingQuestion()->getKey()"
                                                                     :question-uuid="$this->getDiscussingQuestion()->uuid"
                                            />
                                        @endforeach
                                    </div>

                                    <div class="max-w-full">
                                        {!! $this->getDisplayableQuestionText()  !!}
                                    </div>
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>

                @endif {{-- end question and group container --}}

                {{-- start answer container--}}
                <x-accordion.container
                        :active-container-key="true ? 'answer' : ''"
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
                                @unless($this->testTake->enable_question_text_colearning)
                                    <div class="inline-flex question-number rounded-full text-center justify-center items-center mr-2 {!! $this->answeredStatus === 'answered' ? 'complete': 'incomplete' !!}">
                                        <span class="align-middle cursor-default">{{ $this->questionOrderNumber }}</span>
                                    </div>
                                @endif
                                @if($this->answerRating->answer->question->type !== 'InfoscreenQuestion')
                                    <h4 class="inline-block">  {{__('co-learning.answer')}} {{ $this->answerFollowUpNumber }}
                                        :</h4>
                                @endif
                                <h4 class="inline-block ml-2 mr-6"
                                    selid="questiontitle">{{ $this->answerRating->answer->question->type_name }}</h4>
                                @if($this->answerRating->answer->question->type !== 'InfoscreenQuestion')
                                    <h7 class="inline-block">max. {{ $this->answerRating->answer->question->score }}
                                        pt
                                    </h7>
                                    <x-dynamic-component :component="$this->answeredStatus"/>
                                @endif
                            </div>
                            {{-- END TITLE --}}
                        </x-slot:title>
                        <x-slot:body>
                            <div class="student-answer | w-full | questionContainer"
                                 wire:key="student-answer-{{$this->getDiscussingQuestion()->uuid.'-'.$this->answerRating->answer->uuid}}-{{$this->answerFeedbackFilter}}"
                            >
                                <x-dynamic-component
                                        :component="'answer.student.'. str($this->answerRating->answer->question->type)->kebab()"
                                        :question="$this->getDiscussingQuestion()"
                                        :answer="$this->answerRating->answer"
                                        :answerRating="$this->answerRating"
                                        :editorId="'ar-'.$this->answerRating->getKey()"
                                        :inCoLearning="true"
                                        :inAssessment="false"
                                        :disabled-toggle="false"
                                        {{-- webspellchecker plugin --}}
                                        :webSpellChecker="$this->testTake->enable_spellcheck_colearning"
                                        {{-- comments plugin --}}
                                        :enableComments="$this->testTake->enable_comments_colearning"
                                        :commentMarkerStyles="$this->commentMarkerStyles"
                                        :answerFeedbackFilter="$this->answerFeedbackFilter"

                                />
                            </div>
                        </x-slot:body>
                    </x-accordion.block>
                </x-accordion.container> {{-- end answer container--}}

                @if($this->testTake->enable_answer_model_colearning)
                    {{-- start answer model --}}
                    <x-accordion.container :active-container-key="true ? 'answer-model' : ''"
                                           :wire:key="'answer-model-section-'. $uniqueKey"
                    >
                        <x-accordion.block key="answer-model"
                                           :coloredBorderClass="'primary'"
                                           :emitWhenSet="true"
                                           :wire:key="'answer-model-section-block'. $uniqueKey"
                        >
                            <x-slot:title>
                                <div class="question-indicator items-center flex">
                                    <h4 class="inline-block"
                                        selid="questiontitle">@lang('co-learning.answer_model')</h4>
                                </div>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="w-full questionContainer"
                                     wire:key="answer-model-{{$this->getDiscussingQuestion()->uuid}}">
                                    <x-dynamic-component
                                            :component="'answer.teacher.'. str($this->getDiscussingQuestion()->type)->kebab()"
                                            :question="$this->getDiscussingQuestion()"
                                            :editorId="'editor-'.$this->getDiscussingQuestion()->uuid"
                                            :testTake="$this->testTake"
                                            :answer="$this->answerRating->answer"
                                            :student-answer-rating="$this->answerRating"
                                    />
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                @endif {{-- end answer model --}}
            </div>
        @endif


        <x-slot name="testName">{{  $testTake->test->name }}</x-slot>

        @if($waitForTeacherNotificationEnabled && !$this->selfPacedNavigation)
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
                                                  :disabled="$scoreSliderDisabled"
                                                  :co-learning="true"
                            />
                        </div>
                    @endif
                </div>
                <div class="flex items-center space-x-4 text-right">
                    <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span>
                    <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionOrderNumber}}</b>/{{$this->numberOfQuestions}}</span>
                    {{--
                        - previous answer/question becomes a text button
                        - next answer/question is a primary button
                        - previous/next answers have a chevron icon
                        - previous/next questions have an arrow icon
                    --}}
                    @php($clearCkeditor = \Illuminate\Support\Js::from($this->answerRating->answer->question->isType('OpenQuestion')))
                    @if($this->isPreviousQuestionButtonVisible())
                        <x-button.text class="rotate-svg-180 previous-question-btn whitespace-nowrap"
                                       x-on:click="await goToPreviousQuestion({{ $clearCkeditor }})"
                                       wire:loading.attr="disabled"
                        >
                            <x-icon.arrow/>
                            <span>{{ __('test_take.previous_question') }}</span>
                        </x-button.text>
                    @endif

                    @if($this->isPreviousAnswerRatingButtonVisible())
                        <x-dynamic-component :component="$selfPacedNavigation ? 'button.text' : 'button.primary' "
                                             class="rotate-svg-180 previous-answer-rating-btn whitespace-nowrap !min-w-10"
                                             x-on:click="await goToPreviousAnswerRating({{ $clearCkeditor }})"
                                             wire:loading.attr="disabled"
                        >
                            <x-icon.chevron/>
                            <span>{{ __('co-learning.previous_answer') }}</span>
                        </x-dynamic-component>
                    @endif

                    @if($this->isNextAnswerRatingButtonVisible())
                        <x-button.primary x-on:click="await goToNextAnswerRating({{ $clearCkeditor }})"
                                          wire:loading.attr="disabled"
                                          class="next-answer-rating-btn whitespace-nowrap"
                                          :disabled="$this->isNextButtonDisabled()"
                        >
                            <span>{{ __('co-learning.next_answer') }}</span>
                            <x-icon.chevron/>
                        </x-button.primary>
                    @endif

                    @if($this->isNextQuestionButtonVisible())
                        <x-button.primary x-on:click="await goToNextQuestion({{ $clearCkeditor }})"
                                          wire:loading.attr="disabled"
                                          class="next-question-btn whitespace-nowrap"
                                          :disabled="$this->isNextButtonDisabled()"
                        >
                            <span>{{ __('test_take.next_question') }}</span>
                            <x-icon.arrow/>
                        </x-button.primary>
                    @endif

                    {{-- TODO Finish CO-Learning button --}}
                    @if($finishCoLearningButtonEnabled)
                        <x-button.cta x-on:click="await goToFinishedCoLearningPage"
                                      wire:loading.attr="disabled"
                                      class="finish-co-learning-btn"
                        >
                            <x-icon.checkmark/>
                            <span>{{ __('co-learning.finish') }}</span>
                        </x-button.cta>
                    @endif

                </div>
            @endif
        </footer>
    </div>
    @if($testTake->enable_comments_colearning && !$coLearningFinished)
        <x-partials.co-learning-drawer
                uniqueKey="ar-{{ $this->answerRating->getKey() }}-question-{{$this->getDiscussingQuestion()->uuid}}-{{ $this->answerFollowUpNumber }}-{{$this->getAnswerFeedbackUpdatedStateHash()}}">
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
                             x-init="createFocusableButtons(); $dispatch('reinitialize-editor-{{ 'feedback-editor-'. $this->questionOrderNumber .'-'. $this->answerFollowUpNumber }}')"
                        >
                            @if($this->getDiscussingQuestion()->isType('OpenQuestion'))
                                <x-input.comment-color-picker
                                        commentThreadId="new-comment"
                                        uuid="new-comment"
                                        :useCkEditorView="true"
                                ></x-input.comment-color-picker>
                            @else
                                <x-partials.comment-emoji-templates/>
                            @endif


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
                                       x-on:click="ClassicEditors['feedback-editor-{{ $this->questionOrderNumber }}-{{ $this->answerFollowUpNumber }}'].focus()"
                                >
                                    @lang('assessment.Feedback schrijven')
                                </label>
                                <x-input.rich-textarea type="create-answer-feedback"
                                                       :editorId="'feedback-editor-'. $this->questionOrderNumber .'-'. $this->answerFollowUpNumber"
                                />
                            </div>

                            <div class="flex justify-end space-x-4 h-fit mt-2 mb-6 items-center"
                                 x-on:button-cancel-clicked="resetAddNewAnswerFeedback(true)"
                                 x-on:button-save-clicked="createCommentThread()"
                                 id="saveNewFeedbackButtonWrapper"
                                 data-save-translation="@lang('general.save')"
                                 data-cancel-translation="@lang('modal.annuleren')"
                                 data-answer-editor-id="{{ 'ar-'. $this->answerRating->getKey() }}"
                                 data-feedback-editor-id="{{ 'feedback-editor-'. $this->questionOrderNumber .'-'. $this->answerFollowUpNumber }}"
                            > {{-- filled by javascript with Ckeditor view components, cancel and save button --}}
                            </div>
                        </div>
                    </div>

                    <div class="answer-feedback-given-comments | relative">
                        <button class="flex bold border-t border-blue-grey py-2 justify-between items-center w-full group"
                                :class="{'text-midgrey': !hasFeedback || $store.answerFeedback.creatingNewComment !== false}"
                                x-init="dropdownOpened = hasFeedback ? dropdownOpened : 'add-feedback'"
                                @click="toggleFeedbackAccordion('given-feedback')"
                                :disabled="!hasFeedback || $store.answerFeedback.creatingNewComment !== false"
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