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

    <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
        @if(!$this->cannotViewFooterInformation)
            <x-input.score-slider class="" model-name="rating" :max-score="$maxRating" :score="$rating"/>
        @else
            <div></div>
        @endif
        <div class="flex items-center space-x-2">
            @if(!$this->cannotViewFooterInformation)
                <span><b class="bold">{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span> {{--todo add counter and translation --}}
                <span><b class="bold">{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>
            @endif


            <x-button.primary wire:click="goToNextAnswerRating()" :disabled="$rating !== null ? false : true ">
                {{ __('co-learning.next_answer') }}
                <x-icon.arrow class="ml-2"/>
            </x-button.primary>

        </div>
    </footer>


</div>
