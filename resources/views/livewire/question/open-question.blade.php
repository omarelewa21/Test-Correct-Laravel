<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div
                x-data="{count:0, closed: @entangle('closed') }"
                x-init="count = $refs.countme.value.length;"
                class="relative"
        >
            <div questionHtml wire:ignore>{!!   $question->converted_question_html !!}</div>

            <div class="flex-col relative mt-4">
                <x-input.group for="me" label="{!! __('test_take.instruction_open_question') !!}"
                               class="w-full">
                    <div id="hidden_span_{{ $question->id }}"  class="hidden">{!! $this->answer !!}</div>
                    <x-input.textarea
                            id="textarea_{{ $question->id }}"
                            wire:key="textarea_{{ $question->id }}"
                            wire:model.lazy="answer"
                            x-ref="countme"
                            x-on:keyup="count = $refs.countme.value.length"
                            style="min-height:80px "
                            name="name"
                            maxlength="140"
                            spellcheck="false"
                            @focus="handleFocusForReadspeaker()"
                            @blur="handleBlurForReadspeaker()"
                    ></x-input.textarea>
                </x-input.group>
                <div class="absolute bg-blue-grey rounded-lg overflow-hidden "
                     style="height: 10px; width: calc(100% - 4px);left:2px; bottom: 2px">
                    <template x-if="!closed">
                        <span :style="calculateProgress(count, $refs.countme.maxLength)"
                              class="transition bg-primary absolute h-2 border border-primary rounded-lg">
                        </span>
                    </template>
                </div>
            </div>

            <div class="mt-1 primary text-sm bold">
                <span x-html="count"></span> / <span x-html="closed ? '' : $refs.countme.maxLength"></span>
                <span>{!! __('test_take.characters') !!}</span>
                @if(Auth::user()->text2speech)
                    <a class="float-right" role="button" x-on:click="readTextArea('{{ $question->id }}')">
                        <x-icon.audio/>
                    </a>
                @endif
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
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>


