<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div
                x-data="{count:0, closed: @entangle('closed') }"
                x-init="count = $refs.countme.value.length;"
                class="relative"
        >
            <div questionHtml wire:ignore>{!!   $question->converted_question_html !!}</div>

            <div class="flex-col relative mt-4">
                <label for="me" class="transition ease-in-out duration-150">{!! __('test_take.instruction_open_question') !!}</label>
                <x-input.group for="me"
                               class="w-full">
                    <div id="hidden_span_{{ $question->id }}"  class="hidden">{!! $this->answer !!}</div>
                    <x-input.textarea
                            id="textarea_{{ $question->id }}"
                            wire:key="textarea_{{ $question->id }}"
                            wire:model.lazy="answer"
                            x-ref="countme"
                            x-on:keyup="count = $refs.countme.value.length"
                            x-on:focus="handleFocusForReadspeaker"
                            x-on:blur="handleBlurForReadspeaker"
                            style="min-height:80px "
                            name="name"
                            maxlength="140"
                            spellcheck="false"
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
                document.addEventListener('readspeaker_opened', () => {
                    var oldEl = document.getElementById('there_can_only_be_one');
                    var possibleTextarea = false;
                    var hidden_div;
                    if(oldEl){
                        possibleTextarea = oldEl.nextElementSibling;
                    }
                    if(possibleTextarea.id!='textarea_{{ $question->id }}') {
                        createHiddenDivTextArea({{ $question->id }});
                    }
                })
            </script>
        @endpush
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>


