<div class="flex flex-col w-full" spellcheck="false">
    <div class="w-full"
         @unless($studentAnswer && $enableComments) wire:ignore @endif
         x-data="{
            editorId: @js($editorId),
            handleExpand(event) {
                if(this.$el.closest('[data-block-id]').dataset.blockId === event.detail.id) {
                    this.$nextTick(() => this.$dispatch('reinitialize-editor-'+this.editorId))
                }
            },
            setAnswerFeedbackFilter(event) {
                console.log($el.children); /* TODO fix answer Feedback filter */

                /* todo idea: get dimensions of $el, make a loading div and remove the div to make sure all eventlisteners are gone */

                Array.from($el.children).forEach((c) => c.remove());
                $wire.setAnswerFeedbackFilter(event.detail.filter);
            },
         }"
         x-on:block-expanded.window="handleExpand($event)"
         x-on:answer-feedback-filter-changed.window="setAnswerFeedbackFilter($event)"
    >

        <x-input.group for="me" class="w-full disabled mt-4">
            @if($studentAnswer && $enableComments)
            <x-input.comment-editor
                    type="answer-open-question"
                    :allowWsc="$webSpellChecker"
                    :editor-id="$editorId"
                    :restrictWords="$question->restrict_word_amount"
                    :maxWords="$question->max_words"
                    :textFormatting="$question->text_formatting"
                    :mathmlFunctions="$question->mathml_functions"
                    :lang="$question->lang"
                    :answerId="$answer->getKey()"
                    :commentMarkerStyles="$commentMarkerStyles"
                    :answerFeedbackFilter="$answerFeedbackFilter"
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