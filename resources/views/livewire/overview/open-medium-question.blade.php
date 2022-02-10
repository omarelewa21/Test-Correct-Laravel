<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="w-full"
         x-data="{ }"
         x-init="

                      ">

        <div class="flex-col space-y-3">
            <div>
                {!! $question->converted_question_html !!}
            </div>
            <x-input.group wire:ignore class="w-full">
                <x-input.textarea autofocus="true" id="{{ $editorId }}" name="{{ $editorId }}"
                                  wire:model="answer">

                </x-input.textarea>
            </x-input.group>
        </div>
    </div>
</x-partials.overview-question-container>


