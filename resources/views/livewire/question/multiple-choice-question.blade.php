<div class="flex flex-col p-8 sm:p-10 content-section" x-data="{ showMe: false }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6">{!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>
    <div>
        {!! $question->getQuestionHtml()  !!}
        <div class="mt-4 space-y-2 w-1/2">
            @foreach( $question->multipleChoiceQuestionAnswers as $link)
                <div class="flex items-center mc-radio">
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
    </div>
</div>
