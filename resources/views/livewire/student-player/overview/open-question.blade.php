<x-partials.overview-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="w-full">
        <div class="relative">
            <div questionHtml wire:ignore
                 style="width: 100%; display: inline-block">{!!   $question->converted_question_html !!}</div>
            <label for="{{ $this->editorId }}"
                   class="transition ease-in-out duration-150 mt-4">{!! __('test_take.instruction_open_question') !!}</label>
            <x-input.group :for="$this->editorId" class="w-full disabled">
                <x-input.rich-textarea
                        type="student"
                        wire:key="textarea_{{ $question->id }}"
                        :allowWsc="false"
                        :lang="$this->question->lang"
                        :editor-id="$this->editorId"
                        :restrictWords="$this->question->restrict_word_amount"
                        :maxWords="$this->question->max_words"
                        :textFormatting="$this->question->text_formatting"
                        :mathmlFunctions="$this->question->mathml_functions"
                        :disabled="true"
                >{!!  $this->answer !!}</x-input.rich-textarea>
            </x-input.group>
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
        </div>
    </div>
</x-partials.overview-question-container>

