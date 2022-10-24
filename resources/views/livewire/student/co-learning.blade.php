<div class="flex flex-col w-full pt-12">


    @switch($this->answerRating->answer->question->type)
        @case('RankingQuestion')
        @case('OpenQuestion')
        @default
            <livewire:co-learning.open-question
                    :answerRating="$this->answerRating"
                    :questionNumber="$questionFollowUpNumber"
                    :answerNumber="$answerFollowUpNumber"
                    wire:key="'q-'.$testQuestion->uuid'"
            />
    @endswitch

    <x-slot name="testName">{{ $testTake->test->name }}</x-slot>

    <footer class="footer px-8 flex content-center justify-between fixed w-full bottom-0 left-0 z-10">
        <x-input.score-slider class="" wire:model.debounce.300ms="rating" score="{{$rating}}" :max-score="$maxRating"/>

        <div class="flex items-center space-x-2">
            <span><b>{{ __('co-learning.answer') }} {{ $this->answerFollowUpNumber }}</b>/{{ $this->numberOfAnswers }}</span> {{--todo add counter and translation --}}
            <span><b>{{ __('co-learning.question') }} {{$this->questionFollowUpNumber}}</b>/{{$this->numberOfQuestions}}</span>

            <x-button.primary wire:click="goToNextAnswerRating()">
                {{ __('co-learning.next_answer') }}
                <x-icon.arrow class="ml-2"/>
            </x-button.primary>

        </div>
    </footer>



</div>
