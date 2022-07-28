<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3 children-block-pdf">
            {!! $question->converted_question_html !!}
        </div>
        <div class="mt-3 question-no-break-drawing drawing-img-container">
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="800"
                     src="data:image/png;base64,{{$pngBase64}}" alt="">
                <span></span>

        </div>
        <div class="drawing-paper-container">
            <div class="paper-line-wide"/>
            <div class="paper-line-wide"/>
            <div class="paper-line-wide"/>
            <div class="paper-line-wide"/>
            <div class="paper-line-wide"/>
            <div class="paper-line-wide"/>
        </div>
    </div>
</x-partials.test-print-question-container>
