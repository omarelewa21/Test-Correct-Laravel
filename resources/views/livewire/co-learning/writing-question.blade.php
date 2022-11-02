<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber" :question="$question" >
    <div class="w-full">
        <div class="relative" wire:key="editor-{{$answerRatingId}}">
            <x-input.group for="me" class="w-full disabled mt-4" >
                <x-input.rich-textarea wire:model.debounce.1000ms="answer"
                                       :editor-id="$answerRatingId"
                                       :allow-wsc="true"
                                       type="student-co-learning"
                                       lang="{{$question->lang}}"
                ></x-input.rich-textarea>
            </x-input.group>
            <div class="flex justify-between">
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
                <div class="text-midgrey">
                    <span id="problem-count-{{$answerRatingId}}"></span>
                    <span class="ml-1">Taalfouten</span>
                </div>
            </div>
        </div>
    </div>

    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
</x-partials.co-learning-question-container>
