<x-partials.test-print-question-container :number="$number" :question="$question" :answer="$answer">

    <div class="flex flex-1 flex-col space-y-2">
        <div class="flex flex-col space-y-3 children-block-pdf">
            {!! $question->converted_question_html !!}
        </div>
        @if($pngBase64)
            <div class="mt-3 question-no-break-drawing drawing-img-container">
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="800"
                     src="{{$pngBase64}}" alt="">
                <span></span>
            </div>
        @else
            <div class="mt-3 question-no-break-drawing drawing-img-container">
                    <table class="drawing-question-grid-table" style="width: 965px">
                        @foreach(range(1, $oldDrawingQuestionGridHeight) as $row)
                            <tr>
                                @foreach(range(1, $oldDrawingQuestionGridWidth) as $column)
                                    <td {{ $loop->parent->first && $loop->first ? 'grid-top-left' : '' }}
                                            {{ $loop->parent->first && $loop->last ? 'grid-top-right' : '' }}
                                            {{ $loop->parent->last && $loop->first ? 'grid-bottom-left' : '' }}
                                            {{ $loop->parent->last && $loop->last ? 'grid-bottom-right' : '' }}
                                    >
                                        &nbsp;
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </table>
            </div>
        @endif
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
