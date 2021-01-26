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
    <div>
        {!! $question->getQuestionHtml()  !!}

        @if($question->subtype === 'MultipleChoice')
            <div class="mt-4 space-y-2 w-1/2">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)
                    <div class="flex items-center flex-col">
                        <label
                                for="link{{ $link->id }}"
                                class="
                         relative
                         w-full
                          flex
                          hover:font-bold
                           p-5 border
                           border-blue-grey
                           rounded-10
                            base
                            multiple-choice-question
                            transition
                            ease-in-out duration-150
                            focus:outline-none
                            justify-between
                            {!! ($this->answer == $link->id) ? 'active' :'' !!}
                                        "
                        >
                            <input
                                    wire:model="answer"
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $link->id }}"
                            >
                            <div>{!! $link->answer !!}</div>
                            <div class="{!! ($this->answer == $link->id) ? '' :'hidden' !!}">
                                <x-icon.checkmark></x-icon.checkmark>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        @if($question->subtype === 'TrueFalse')
            <div class="mt-4 flex divide-x">
                @foreach( $question->multipleChoiceQuestionAnswers as $link)
                    <div class="flex bg-off-white">
                        <label for="link{{ $link->id }}"
                               class="
                            bg-off-white
                            border border-blue-grey rounded-lg
                            trueFalse
                            @if($loop->iteration == 1) rounded-r-none border-r-0 true @else rounded-l-none border-l-0 false @endif"
                        >
                            <input
                                    wire:model="answer"
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $link->id }}">

                            <span>{!! $link->answer !!}</span>
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
