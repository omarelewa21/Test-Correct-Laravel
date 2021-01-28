@props([
    'questions',
    'showOverview'
])

<div class="question-indicator w-full">
    <div class="flex flex-wrap" x-data="">
        @foreach($questions as $key => $q)
            <div
                wire:key="nav_{{$key}}"

                class="question-number rounded-full text-center {!! $key === ($this->q - 1) ? 'active' : ''!!}"
                 wire:click="$set('q',{{ 1+$key}})"

                x-on:click="$dispatch('current-updated', {'current': {{ 1+$key }} })"

            >
                <span class="align-middle">{{ ++$key }}</span>
            </div>
        @endforeach

        <section class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            <x-button.text-button href="#" wire:click="sendNotification">
                <x-icon.audio/>
                <span>{{ __('test_take.speak') }}</span>
            </x-button.text-button>

            <x-button.text-button wire:click="overview" href="#">
                <x-icon.preview/>
                <span>{{ __('test_take.overview') }}</span>
            </x-button.text-button>

        </section>
    </div>
</div>
