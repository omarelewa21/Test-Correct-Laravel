<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">
    <div class="flex flex-1 flex-col space-y-2">
        <div>{!! $question->converted_question_html !!}</div>
        <div class="flex flex-col pdf-100 max-w-max space-y-2">
            @foreach($answerStruct as $answer)
                <div class="bg-light-grey mt-2 base border-light-grey border-2
                                                                 rounded-10 inline-flex px-4 py-1.5 items-center justify-between drag-item bold font-size-18 pdf-80 pdf-minh-40"
                >
                    <span class="mr-3 flex items-center pdf-align-center" >{{ $answerText[$answer->value] }}</span>
                    <div class="w-4">
                        <x-icon.drag-pdf/>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-partials.answer-model-question-container>

