<div class="flex w-full">
    <div class="flex-col bg-white mt-12 w-full h-fit border pl-8 rounded-10 p-4 relative">
        <div class="absolute w-0 h-full border-yellow-300 border-4 top-0 left-0 rounded-10"></div>
        <div class="w-full flex">
            <h1> {!! $this->answerRating->answer->question->getQuestionInstance()->question !!}</h1>
        </div>
        {{-- The whole world belongs to you. --}}
        {{--    <livewire:co-learning.open-question--}}
        {{--            :question="$testQuestion"--}}
        {{--            :number="++$key"--}}
        {{--            :answers="$answers"--}}
        {{--            wire:key="'q-'.$testQuestion->uuid'"--}}
        {{--    />--}}


        <x-button.cta @click="$dispatch('notify', {message: 'hello'})">test</x-button.cta>


        <x-input.score-slider class="mt-4" wire:model="rating" score="{{$rating}}" :max-score="$maxRating"/>

        <x-slot name="fraudDetection">
        </x-slot>

        <x-slot name="footerbuttons">
            <span><b>antwoord x</b>/x</span>
            <span><b>vraag x</b>/x</span>
            @if($nextAnswerAvailable)
                <x-button.cta wire:click="getNextAnswerRating()">
                    Next Answer
                    <x-icon.arrow class="ml-2"/>
                </x-button.cta>
            @endif
        </x-slot>

    </div>
</div>
