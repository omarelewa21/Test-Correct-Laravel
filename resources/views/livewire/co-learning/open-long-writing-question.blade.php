<x-partials.co-learning-question-container :question="$question" >
    <div class="w-full">
        <div class="relative" wire:key="editor-{{$answerRatingId}}">
            <x-input.group for="me" class="w-full disabled mt-4" >
                <x-input.rich-textarea wire:model.debounce.1000ms="answer"
                                       :editor-id="'ar-'.$answerRatingId"
                                       :allow-wsc="$webSpellChecker"
                                       type="student-co-learning"
                                       lang="{{$question->lang ?? 'nl_NL'}}"
                ></x-input.rich-textarea>
            </x-input.group>
            <div class="flex justify-between" wire:ignore>
                <div class="flex space-x-2 text-midgrey">
                    <div>
                        <span class="mr-1">Woorden: </span>
                        <span id="word-count-{{$answerRatingId}}" class="min-w-[1rem]">&nbsp;</span>
                    </div>
                    <div>
                        <span class="mr-1">Tekens: </span>
                        <span id="char-count-{{$answerRatingId}}" class="min-w-[1rem]">&nbsp;</span>
                    </div>
                </div>
                @if($webSpellChecker)
                <div class="text-midgrey">
                    <span id="problem-count-{{$answerRatingId}}"></span>
                    <span class="ml-1">Taalfouten</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
</x-partials.co-learning-question-container>
