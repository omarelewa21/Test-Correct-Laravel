@props([
    'questions',
])

<div class="question-indicator w-full">
    <div class="flex flex-wrap">
        @foreach($questions as $key => $question)
            <div class="question-number rounded-full text-center complete" wire:click.prevent="setMainQuestion({{ $key }})">
                <span class="align-middle">{{ ++$key}}</span>
            </div>
        @endforeach

        <section class="flex space-x-6 ml-auto min-w-max justify-end items-center">
            <x-button.text-button href="#">
                <x-icon.audio/>
                <span>Lees voor</span>
            </x-button.text-button>

            <x-button.text-button href="#">
                <x-icon.preview/>
                <span>Bekijk antwoorden</span>
            </x-button.text-button>
        </section>
    </div>
</div>
