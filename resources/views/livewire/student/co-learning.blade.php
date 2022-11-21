<div id="co-learning-page"
     class="flex flex-col w-full pt-12"
     wire:poll.keep-alive.15000ms="updateHeartbeat()"
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
                            :questionNumber="$questionFollowUpNumber"
                            :answerNumber="$answerFollowUpNumber"
                            wire:key="q-{{$testTake->discussingQuestion->uuid}}"
                    />
                </div>
            @else
                <div class="w-full">
                    @switch($this->answerRating->answer->question->type)
                        @case('CompletionQuestion')
                            <livewire:co-learning.completion-question
                                    :answerRatingId="$this->answerRating->getKey()"
                                    :questionNumber="$questionFollowUpNumber"
                                    :answerNumber="$answerFollowUpNumber"
                                    :wire:key="'ar-'. $this->answerRating->getKey()"
                            />
                            @break
                        @case('DrawingQuestion')
                            <livewire:co-learning.drawing-question
                                    :answerRatingId="$this->answerRating->getKey()"
                                    :questionNumber="$questionFollowUpNumber"
                                    :answerNumber="$answerFollowUpNumber"
                                    :wire:key="'ar-'. $this->answerRating->getKey()"
                            />
                            @break
                        @case('OpenQuestion')
                            <livewire:co-learning.open-question
                                    :answerRatingId="$this->answerRating->getKey()"
                                    :questionNumber="$questionFollowUpNumber"
                                    :answerNumber="$answerFollowUpNumber"
                                    :wire:key="'ar-'. $this->answerRating->getKey()"
                            />
                            @break
                        @default
                    @endswitch
                </div>
            @endif
        </div>
    @endif
    <x-slot name="testName">{{ $testTake->test->name }}</x-slot>

    @if(!$coLearningFinished && $waitForTeacherNotificationEnabled)
        <div class="fixed  right-1/2 translate-x-1/2 top-[93px] px-2 shadow border informational rounded leading-7 bold flex items-center">
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
                                              :allow-half-points="$allowRatingWithHalfPoints"
                                              :disabled="!$this->answerRating->answer->isAnswered"
                        />
                    </div>
                @endif
            </div>
            <div class="flex items-center space-x-2">
                <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span>
                <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>

                @if($previousAnswerAvailable)
                    <x-button.primary class="rotate-svg-180" wire:click="goToPreviousAnswerRating()">
                        <x-icon.arrow class=""/>
                        <span>{{ __('co-learning.previous_answer') }}</span>
                    </x-button.primary>
                @elseif($nextAnswerAvailable)
                    @if($this->enableNextQuestionButton)
                        <x-button.primary wire:click="goToNextAnswerRating()">
                            <span>{{ __('co-learning.next_answer') }}</span>
                            <x-icon.arrow/>
                        </x-button.primary>
                    @else
                        <x-button.primary wire:click="goToNextAnswerRating()" disabled>
                            <span>{{ __('co-learning.next_answer') }}</span>
                            <x-icon.arrow/>
                        </x-button.primary>
                    @endif
                @endif

                @if($finishCoLearningButtonEnabled)
                    <x-button.cta wire:click="goToFinishedCoLearningPage">
                        {{ __('co-learning.finish') }}
                    </x-button.cta>
                @endif

            </div>
        @endif
    </footer>


</div>
