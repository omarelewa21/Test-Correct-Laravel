<div class="flex flex-col p-8 sm:p-10 content-section" >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>

    <div class="flex flex-1">

        <div class="w-full">
            {!! $question->getQuestionHtml()  !!}
            <div class="mt-4 space-y-2 w-1/2">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)
                    <div class="flex items-center mc-radio">
                        <label
                                for="link{{ $link->id }}"
                                class="relative w-full flex hover:font-bold p-5 border-2 border-blue-grey rounded-10 base
                            multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                            justify-between {!! ($this->answer == $link->id) ? 'active' :'disabled' !!}"
                        >
                            <input
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $link->id }}"
                            >
                            <div>{!! $link->answer !!}</div>
                            <div class="{!! ($this->answer == $link->id) ? '' :'hidden' !!}">
                                <x-icon.checkmark/>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

