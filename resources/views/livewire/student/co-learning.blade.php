<div class="flex flex-col w-full pt-12">

    <div class="flex flex-col w-full" wire:key="q-{{$testTake->discussingQuestion->uuid}}">
        @if($this->informationScreenQuestion)
            <div class="w-full">
                <livewire:co-learning.info-screen-question
                        :question="$this->testTake->discussingQuestion"
                        :questionNumber="$questionFollowUpNumber"
                        :answerNumber="$answerFollowUpNumber"
                />
            </div>
        @else
            <div class="w-full" wire:key="ar-{{$this->answerRating->getKey()}}">
                @switch($this->answerRating->answer->question->type)
                    @case('CompletionQuestion')
                        <livewire:co-learning.completion-question
                                :answerRatingId="$this->answerRating->getKey()"
                                :questionNumber="$questionFollowUpNumber"
                                :answerNumber="$answerFollowUpNumber"
                        />
                        @break
                    @case('DrawingQuestion')
                        <livewire:co-learning.drawing-question
                                :answerRatingId="$this->answerRating->getKey()"
                                :questionNumber="$questionFollowUpNumber"
                                :answerNumber="$answerFollowUpNumber"
                        />
                        @break
                    @case('OpenQuestion')
                        <livewire:co-learning.open-question
                                :answerRatingId="$this->answerRating->getKey()"
                                :questionNumber="$questionFollowUpNumber"
                                :answerNumber="$answerFollowUpNumber"
                        />
                        @break
                    @default
                @endswitch
            </div>
        @endif
    </div>

    <x-slot name="testName">{{ $testTake->test->name }}</x-slot>
    @if($studentWaitForTeacher)
        <div class="absolute right-1/2 translate-x-1/2 top-[93px] px-2 shadow border informational rounded leading-7 bold flex items-center">
            <x-icon.time-dispensation/>
            <span class="ml-2">{{ __('co-learning.wait_for_teacher') }}</span>
        </div>
    @endif
    {{ $rating }}
    <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
        @if(!$this->informationScreenQuestion)
            <div class="flex content-center justify-between" wire:key="ar-{{$this->answerRatingId}}">
                <x-input.score-slider class="" model-name="rating" :max-score="$maxRating" :score="$rating"/>
            </div>
        @else
            <div></div>
        @endif
        <div class="flex items-center space-x-2">
            <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span> {{--todo add counter and translation --}}
            <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>

            @if($previousAnswerAvailable && !$nextAnswerAvailable)
                <x-button.primary wire:click="goToPreviousAnswerRating()">
                    {{ __('co-learning.previous_answer') }}
                    <x-icon.arrow class="ml-2"/>
                </x-button.primary>
            @else
                @if(isset($this->rating) && !is_null($rating))
                    <x-button.primary wire:click="goToNextAnswerRating()">
                        {{ __('co-learning.next_answer') }}
                        <x-icon.arrow class="ml-2"/>
                    </x-button.primary>
                @else
                    <x-button.primary wire:click="goToNextAnswerRating()" disabled>
                        {{ __('co-learning.next_answer') }}
                        <x-icon.arrow class="ml-2"/>
                    </x-button.primary>
                @endif
            @endif
            {{-- @todo finish colearning button (CTA) --}}
            @if($finishCoLearningButtonEnabled) {{-- if all answers of the last question have been answered... show finish Co-Learning button --}}
                <x-button.cta>{{ __('co-learning.finish') }}</x-button.cta>
            @endif


        </div>
    </footer>


</div>
