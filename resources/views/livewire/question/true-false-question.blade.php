<x-partials.question-container :number="$number" :q="$q" :question="$question">
    <div class="w-full">
        <div class="flex space-x-4 items-center">
            <div class="inline-flex bg-off-white border border-blue-grey rounded-lg truefalse-container transition duration-150">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)

                        <label for="link{{ $link->id }}"
                               class="bg-off-white border border-off-white rounded-lg trueFalse bold transition duration-150
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                               {!! ($this->answer == $link->id) ? 'active' :'' !!}">
                            <input wire:model="answer"
                                   id="link{{ $link->id }}"
                                   name="Question_{{ $question->id }}"
                                   type="radio"
                                   class="hidden"
                                   value="{{ $link->id }}"
                            >
                            <span>{!! $link->answer !!}</span>
                        </label>

                @endforeach
            </div>
            {!! $question->getQuestionHtml()  !!}
        </div>
    </div>
</x-partials.question-container>
