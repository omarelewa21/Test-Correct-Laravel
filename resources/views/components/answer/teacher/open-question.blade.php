<div class="flex flex-col w-full">
    <div class="w-full"
         wire:ignore
         x-data="{
            editorId: @js($editorId),
            handleExpand(event) {
                if(this.$el.closest('[data-block-id]').dataset.blockId === event.detail.id) {
                    this.$nextTick(() => this.$dispatch('reinitialize-editor-'+this.editorId))
                }
            }
         }"
         x-on:block-expanded.window="handleExpand($event)"
    >
        <x-input.group for="me" class="w-full disabled mt-4">
            @if($studentAnswer) {{--TODO: make property that differiate between editor with comments and without --}}
            @js($answer->getKey())
            <x-input.comment-editor
                    :allowWsc="$webSpellChecker"
                    :editor-id="$editorId"
                    :restrictWords="$question->restrict_word_amount"
                    :maxWords="$question->max_words"
                    :textFormatting="$question->text_formatting"
                    :mathmlFunctions="$question->mathml_functions"
                    :lang="$question->lang"
                    :answerId="$answer->getKey()"
            >{!! $answerValue !!}</x-input.comment-editor>
            @else
            <x-input.rich-textarea
                    type="student-co-learning"
                    :allowWsc="$webSpellChecker"
                    :editor-id="$editorId"
                    :restrictWords="$question->restrict_word_amount"
                    :maxWords="$question->max_words"
                    :textFormatting="$question->text_formatting"
                    :mathmlFunctions="$question->mathml_functions"
                    :lang="$question->lang"
                    :disabled="true"
            >{!! $answerValue !!}</x-input.rich-textarea>
            @endif
        </x-input.group>
        <div class="flex justify-between" wire:ignore>
            <div class="flex space-x-2 text-midgrey">
                <div id="word-count-{{ $editorId }}" class="word-count">
                </div>

            </div>
            @if($webSpellChecker)
                <div class="text-midgrey">
                    <span id="problem-count-{{ $editorId }}"></span>
                    <span>@lang('co-learning.Taalfouten')</span>
                </div>
            @endif
        </div>
    </div>
</div>