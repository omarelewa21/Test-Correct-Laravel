<x-partials.question-container :number="$number" :question="$question">
    <div class="w-full space-y-3"
         x-on:visible-component.window="
         if($event.detail.el === $el){
            $dispatch('add-width-to-drag-item');
         }"
         x-on:add-width-to-drag-item.window="
             setTimeout(() => {
                 var rankingBody = document.querySelector('#rq{{$question->getKey()}}')
                    if(showMe) {
                        rankingBody.querySelectorAll('.drag-item').forEach(function(item) {
{{--                            item.style.width = rankingBody.offsetWidth+1+'px';--}}
                        });
                    }
             } ,100);
        "
    >
        <div questionHtml wire:ignore>{!! $question->converted_question_html !!}</div>
        <div>
            <span>{!! __('test_take.instruction_ranking_question') !!}</span>
        </div>
        <div id="rq{{ $question->getKey() }}" class="flex flex-col max-w-max space-y-2 ranking"
             wire:sortable="updateOrder" wire:model="answerStruct">
            @foreach($answerStruct as $answer)
                <x-drag-item id="ranking-{{$answer->value}}"
                             sortId="{{ $answer->value }}"
                             wire:key="option-{{ $answer->value }}"
                             selid="drag-box"
                             sortableHandle="false"
                >
                    {{ html_entity_decode($answerText[$answer->value]) }}
                </x-drag-item>
            @endforeach
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
