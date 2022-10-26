<div class="flex flex-col w-full pt-12">

    @if($this->cannotViewFooterInformation)
        <div class="w-full" wire:key="t">
            <livewire:co-learning.info-screen-question
                    :question="$this->testTake->discussingQuestion"
                    :questionNumber="$questionFollowUpNumber"
                    :answerNumber="$answerFollowUpNumber"
            />
        </div>
    @else
        <div class="w-full" wire:key="ar-{{$this->answerRating->getKey()}}">
            @switch($this->answerRating->answer->question->type)
                @case('RankingQuestion')
                @case('OpenQuestion')
                    <livewire:co-learning.open-question
                            :answerRatingId="$this->answerRating->getKey()"
                            :questionNumber="$questionFollowUpNumber"
                            :answerNumber="$answerFollowUpNumber"
                    />
                @default
            @endswitch
        </div>
    @endif
    <x-slot name="testName">{{ $testTake->test->name }}</x-slot>
    @if($studentWaitForTeacher)
        <div class="absolute right-1/2 translate-x-1/2 top-[93px] px-2 shadow border informational rounded leading-7 bold flex items-center">
            <x-icon.time-dispensation/>
            <span class="ml-2">{{ __('co-learning.wait_for_teacher') }}</span>
        </div>
    @endif

    <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
        @if(!$this->cannotViewFooterInformation)
            <div class="flex content-center justify-between"  wire:key="ar-{{$this->answerRatingId}}">
                <x-input.score-slider class="" model-name="rating" :max-score="$maxRating" :score="$rating"/>
            </div>
        @else
            <div></div>
        @endif
        <div class="flex items-center space-x-2">
            @if(!$this->cannotViewFooterInformation)
                <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span> {{--todo add counter and translation --}}
                <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>
            @endif

            @if($previousAnswerAvailable && !$nextAnswerAvailable)
                <x-button.primary wire:click="goToPreviousAnswerRating()">
                    {{ __('co-learning.previous_answer') }}
                    <x-icon.arrow class="ml-2"/>
                </x-button.primary>
            @else
                @if($rating)
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
        </div>
    </footer>


</div>
