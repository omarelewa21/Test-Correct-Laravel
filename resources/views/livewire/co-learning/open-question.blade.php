<x-partials.co-learning-question-container :question="$question" >
    <div class="w-full">
        <div class="relative" wire:key="editor-ar-{{$answerRatingId}}">
            <x-input.group for="me" class="w-full disabled mt-4" >
                <x-input.rich-textarea
                        type="student-co-learning"
                        :allowWsc="$this->question->spell_check_available"
                        :editor-id="'ar-'.$answerRatingId"
                        :restrictWords="$this->question->restrict_word_amount"
                        :maxWords="$this->question->max_words"
                        :textFormatting="$this->question->text_formatting"
                        :mathmlFunctions="$this->question->mathml_functions"
                        :lang="$this->question->lang"
                        :disabled="true"
                >{!! $this->answer !!}</x-input.rich-textarea>
            </x-input.group>
            <div class="flex justify-between" wire:ignore>
                <div class="flex space-x-2 text-midgrey">
                    <div id="word-count-ar-{{$answerRatingId}}" class="word-count"></div>
                </div>
                @if($this->question->spell_check_available)
                <div class="text-midgrey">
                    <span id="problem-count-ar-{{$answerRatingId}}"></span>
                    <span>@lang('co-learning.Taalfouten')</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
</x-partials.co-learning-question-container>
