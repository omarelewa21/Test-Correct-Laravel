<x-partials.co-learning-question-container :questionNumber="$questionNumber" :answerNumber="$answerNumber" :question="$question" >
    <div class="w-full">
        <div class="relative">
            <x-input.group for="me" class="w-full disabled mt-4" >
                <x-input.rich-textarea wire:model.debounce.1000ms="answer"
                                       :editor-id="$answerRatingId"
                                       :allow-wsc="true"
                                       type="student-co-learning"
                                       lang="{{$question->lang}}"
                ></x-input.rich-textarea>
            </x-input.group>
            <div class="flex justify-between">
                <div>
                    <span>Woorden:</span>
                    <span id="word-count-{{$answerRatingId}}" x-ref="word-count-{{$answerRatingId}}"></span>
                    <span>Tekens:</span>
                    <span id="char-count-{{$answerRatingId}}" x-ref="char-count-{{$answerRatingId}}"></span>
                </div>
                <div>
                    <span>Taalfouten</span>
                    <span id="error-count-{{$answerRatingId}}" x-ref="error-count-{{$answerRatingId}}"></span>
                </div>
            </div>
        </div>
    </div>


    <div x-on:contextmenu="$event.preventDefault()" class="absolute z-10 w-full h-full left-0 top-0"></div>
    @push('scripts')
        <script>
            CKEDITOR.on('instanceReady', function(event) {
                var editor = event.editor;
                document.getElementById('word-count-{{$answerRatingId}}').textContent = editor.wordCount.wordCount;
                document.getElementById('char-count-{{$answerRatingId}}').textContent = editor.wordCount.charCount;
                /* todo get the count for the errors
                *   getProblemsCount() is working, but i have to wait for it to exist / webspellchecker to finish checking.
                * */
                /* document.getElementById('error-count-{{$answerRatingId}}').textContent = WEBSPELLCHECKER.getInstances()[0].getProblemsCount() ; */
                document.getElementById('cke_wordcount_{{$answerRatingId}}').classList.add('hidden');
            })
        </script>
    @endpush
</x-partials.co-learning-question-container>
