<x-partials.answer-model-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3 children-block-pdf">
            {!! $question->converted_question_html !!}&nbsp;
        </div>
        <div class="mt-3 flex flex-1 flex-col question-no-break-drawing">
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="600"
                     src="data:image/png;base64,{{$pngBase64}}" alt="">
                <span></span>

        </div>
    </div>
</x-partials.answer-model-question-container>
