<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full">
        <div>
            <div questionHtml wire:ignore
                 style="width: 100%; display: inline-block">{!!   $question->converted_question_html !!}</div>
            <div class="flex-col relative mt-4">
                <label for="{{ $this->editorId }}"
                       class="transition ease-in-out duration-150">{!! __('test_take.instruction_open_question') !!}</label>
                <x-input.group :for="$this->editorId" class="w-full">
                    <x-input.rich-textarea
                            type="student"
                            wire:key="textarea_{{ $question->id }}"
                            :allowWsc="$this->question->spell_check_available"
                            :lang="$this->question->lang"
                            :editor-id="$this->editorId"
                            :restrictWords="$this->question->restrict_word_amount"
                            :maxWords="$this->question->max_words"
                            :textFormatting="$this->question->text_formatting"
                            :mathmlFunctions="$this->question->mathml_functions"
                            :enableGrammar="false"
                    />
                    <div class="flex"
                        x-data="writeDownCms(@js($editorId), @js($this->question->restrict_word_amount), @js($this->question->max_words))"
                        x-on:updated-word-count-plugin-container.window="init()"
                        x-on:selected-word-count.window="addSelectedWordCounter($event.detail, '@lang('question.selected_words')')"
                    >
                        <div id="word-count-{{ $editorId }}"
                            wire:ignore
                            class="word-count note text-sm mt-2 mr-2"
                            x-show="wordCounter"
                        ></div>
                        <div id="selected-word-count-{{ $editorId }}"
                            wire:ignore
                            class="word-count note text-sm mt-2"
                        ></div>
                    </div>
                </x-input.group>
            </div>
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId" />
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>


