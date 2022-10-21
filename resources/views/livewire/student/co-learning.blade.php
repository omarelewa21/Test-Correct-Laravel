<div class="flex flex-col w-full pt-12">

    @php
        $key = 0;
    @endphp

    <livewire:co-learning.open-question
            :answerRating="$this->answerRating"
            :questionNumber="++$key"
            :answerNumber="++$key"
            wire:key="'q-'.$testQuestion->uuid'"
    />


    <div>
        <x-button.cta @click="$dispatch('notify', {message: 'hello'})">test</x-button.cta>
    </div>


        <x-input.score-slider class="mt-4" wire:model="rating" score="{{$rating}}" :max-score="$maxRating"/>



        <x-slot name="fraudDetection">
        </x-slot>

        <x-slot name="footerbuttons">
            <span><b>{{ __('co-learning.answer') }} x</b>/x</span> {{--todo add counter and translation --}}
            <span><b>{{ __('co-learning.question') }} x</b>/x</span>
            @if($nextAnswerAvailable)
                <x-button.cta wire:click="getNextAnswerRating()">
                    Next Answer
                    <x-icon.arrow class="ml-2"/>
                </x-button.cta>
            @endif
        </x-slot>
</div>
