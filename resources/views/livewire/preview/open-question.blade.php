<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div
                x-data="{count:0}"
                x-init="count = $refs.countme.value.length;"
                class="relative"
        >
            <div wire:ignore>{!!   $question->converted_question_html !!}</div>

            <div class="flex-col relative mt-4">
                <x-input.group for="me" label="{!! __('test_take.instruction_open_question') !!}"
                               class="w-full">
                    <x-input.textarea
                            x-on:contextmenu="$event.preventDefault()"
                            spellcheck="false"
                            wire:key="textarea_{{ $question->id }}"
                            style="min-height:80px "
                            name="name"
                            maxlength="140"
                            x-ref="countme"
                            wire:model.lazy="answer"
                            x-on:keyup="count = $refs.countme.value.length"
                    ></x-input.textarea>
                </x-input.group>
                <div class="absolute bg-blue-grey rounded-lg overflow-hidden " style="height: 10px; width: calc(100% - 4px);left:2px; bottom: 2px">
                    <span :style="calculateProgress(count, $refs.countme.maxLength)"
                          class="transition bg-primary absolute h-2 border border-primary rounded-lg">
                    </span>
                </div>
            </div>

            <div class="mt-1 primary text-sm bold">
                <span x-html="count"></span> / <span x-html="$refs.countme.maxLength"></span>
                <span>{!! __('test_take.characters') !!}</span>
            </div>
        </div>

        @push('scripts')
        <script>
            function calculateProgress(count, total) {
                return 'height: 10px; width:' + count / total * 100 + '%';
            }
        </script>
        @endpush
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>


