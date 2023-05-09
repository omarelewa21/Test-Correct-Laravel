<div id="co-learning-page"
     class="flex flex-col w-full pt-12 pb-12"
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
                        {{ __('co-learning.finish') }}
                    </x-button.cta>
                @endif

            </div>
        @endif
    </footer>

</div>
