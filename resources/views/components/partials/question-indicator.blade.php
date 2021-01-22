@props([
    'questions',
    'showOverview'
])

<div class="question-indicator w-full">
    <div class="flex flex-wrap">
        @foreach($questions as $key => $question)
            <div class="question-number rounded-full text-center {!! $this->getState($question->uuid) !!}" wire:click.prevent="setMainQuestion('{{ $question->uuid }}')">
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
