@props([
    'questions',
])

<div class="question-indicator w-full">
    <div class="flex flex-wrap">
        @foreach($questions as $question)
            <div class="question-number rounded-full text-center complete">
                <span class="align-middle">{{$question->order}}</span>
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