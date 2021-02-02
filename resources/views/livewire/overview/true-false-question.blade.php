<div class="flex flex-col p-8 sm:p-10 content-section w-full">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>

        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>
    <div class="w-full overview">
        <div class="flex space-x-4 items-center">
            <div class="inline-flex bg-off-white border @if(!$this->answer) border-all-red @else border-blue-grey @endif rounded-lg overview truefalse-container">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)

                        <label for="link{{ $link->id }}"
                               class="bg-off-white border border-off-white rounded-lg trueFalse bold disabled
                                      @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                               {!! ($this->answer == $link->id) ? 'active' :'' !!}">
                            <input id="link{{ $link->id }}"
                                   name="Question_{{ $question->id }}"
                                   type="radio"
                                   class="hidden"
                                   value="{{ $link->id }}"
                                   disabled
                            >
                            <span>{!! $link->answer !!}</span>
                        </label>
                        @if($loop->first)
                            <div class="@if(!$this->answer) bg-all-red @else bg-blue-grey @endif" style="width: 1px; height: 30px; margin-top: 3px"></div>
                        @endif
                @endforeach
            </div>
            {!! $question->getQuestionHtml()  !!}
        </div>
    </div>
</div>
