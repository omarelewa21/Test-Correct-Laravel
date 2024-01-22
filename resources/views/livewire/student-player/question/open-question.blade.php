<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full"
         x-data="openQuestionStudentPlayer(@js($this->editorId))"
         x-on:sync-editor-data-{{ $this->number }}.window="syncEditorData"
    >
        <div>
            <div questionHtml wire:ignore style="width: 100%; display: inline-block">{!!   $question->converted_question_html !!}</div>
            <div class="flex-col relative mt-4">
                <label for="{{ $this->editorId }}" class="transition ease-in-out duration-150">{!! __('test_take.instruction_open_question') !!}</label>
                <x-input.group :for="$this->editorId"
                               class="w-full"
                >
                    <div id="hidden_span_{{ $question->id }}"  class="hidden">{!! $this->answer !!}</div>

                    <x-input.rich-textarea
                            type="student"
                            wire:key="textarea_{{ $question->id }}"
                            wire:model.debounce.1000ms="answer"
                            :question-id="$this->question->id"
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

                    @if(Auth::user()->text2speech)
                        <div wire:ignore class="rspopup_tlc hidden rsbtn_popup_tlc_{{$question->id}}">
                            <div class="rspopup_play rspopup_btn rs_skip" role="button" tabindex="0"
                                 aria-label="Lees voor" data-rslang="title/arialabel:listen" data-rsevent-id="rs_340375"
                                 title="Lees voor"></div>
                        </div>
                    @endif
                </x-input.group>
            </div>
        </div>
        @push('scripts')
            <script>
                @if(!is_null(Auth::user())&&Auth::user()->text2speech)
                document.addEventListener('readspeaker_closed', () => {
                    if(ReadspeakerTlc.guard.shouldNotReinitCkeditor(document.querySelector( '#{{ $editorId }}' ))){
                        return;
                    }
                    ReadspeakerTlc.ckeditor.reattachReadableAreaAndDestroy('{{ $editorId }}');
                    dispatchEvent(new CustomEvent('reinitialize-editor-{{ $editorId }}'));
                })
                document.addEventListener('readspeaker_started', () => {
                    if(ReadspeakerTlc.guard.shouldNotDetachCkEditor(document.querySelector( '#{{ $editorId }}' ))){
                        return;
                    }
                    RichTextEditor.writeContentToTextarea('{{ $editorId }}');
                    ReadspeakerTlc.ckeditor.detachReadableAreaFromCkeditor('{{ $editorId }}');
                })
                document.addEventListener('readspeaker_opened', () => {
                    if(ReadspeakerTlc.guard.shouldNotCreateHiddenTextarea({{ $question->id }})){
                        return;
                    }
                    const textarea = document.querySelector('#{{ $editorId }}')
                    const editor = ClassicEditors['{{ $editorId }}'];
                    ReadspeakerTlc.rsTlcEvents.fixAriaLabelsForCkeditor(textarea, editor);

                    RichTextEditor.setReadOnly(editor);
                })
                @endif
            </script>
        @endpush
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>


