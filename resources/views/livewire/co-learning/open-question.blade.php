<x-partials.co-learning-question-container :question="$question">
    <div class="w-full" spellcheck="false">
        <div class="relative" wire:key="editor-ar-{{$answerRatingId}}">
            <x-input.group for="me" class="w-full disabled mt-4" >
                @unless($inlineFeedbackEnabled)
                    <x-input.rich-textarea
                            type="student-co-learning"
                            :allowWsc="$webSpellChecker"
                            :editor-id="'ar-'.$answerRatingId"
                            :restrictWords="$this->question->restrict_word_amount"
                            :maxWords="$this->question->max_words"
                            :textFormatting="$this->question->text_formatting"
                            :mathmlFunctions="$this->question->mathml_functions"
                            :lang="$this->question->lang"
                            :disabled="true"
                    >{!! $this->answer !!}</x-input.rich-textarea>
                @else
                    <x-input.comment-editor
                            type="student-co-learning"
                            :allowWsc="$webSpellChecker"
                            :editor-id="'ar-'.$answerRatingId"
                            :restrictWords="$this->question->restrict_word_amount"
                            :maxWords="$this->question->max_words"
                            :textFormatting="$this->question->text_formatting"
                            :mathmlFunctions="$this->question->mathml_functions"
                            :lang="$this->question->lang"
                            :commentMarkerStyles="$commentMarkerStyles"
                            :answerId="$answerId"
                            :answerFeedbackFilter="$answerFeedbackFilter"
                    >{!! $this->answer !!}</x-input.comment-editor>
                @endif
            </x-input.group>
            <div class="flex justify-between" wire:ignore>
                <div class="flex space-x-2 text-midgrey">
                    <div id="word-count-ar-{{$answerRatingId}}" class="word-count"></div>
                </div>
                @if($webSpellChecker)
                <div class="text-midgrey">
                    <span id="problem-count-ar-{{$answerRatingId}}"></span>
                    <span>@lang('co-learning.Taalfouten')</span>
                </div>
                @endif
            </div>
            @unless($inlineFeedbackEnabled)
                <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
            @endif
        </div>
    </div>
</x-partials.co-learning-question-container>
