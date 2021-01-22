<style>
    input[type="radio"]:checked {
        color: red;
    }
</style>

<div class="flex flex-col p-8 sm:p-10 content-section"  x-show="'{{ $question->uuid }}' == current">
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
    </div>

    <div class="flex flex-1">
        <div class="w-full space-y-3">
            <div>
                <span>Lees de stellingen en selecteer de juiste antwoordoptie in de lijst</span>
            </div>
            <div class="flex flex-row space-x-10">
                <div class="flex flex-1 flex-col space-y-6">
                    {!! $question->getQuestionHtml() !!}
                </div>
                <div class="flex flex-1 flex-col">
                    <div>
                        <div class="px-5 space-x-4 text-base bold flex flex-row">
                            <span class="w-16">Optie</span>
                            <span class="w-20">Stelling 1</span>
                            <span class="w-20">Stelling 2</span>
                            <span class="w-10">Reden</span>
                        </div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="space-y-2">
                        @foreach( $question->multipleChoiceQuestionAnswers as $loopCount => $link)
                            <label
                                class="flex p-5 border border-blue-grey rounded-10 base arq-question transition ease-in-out duration-150 focus:outline-none"
                                for="link{{ $link->id }}">
                                <input
                                    wire:model="answer"
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 hidden"
                                    value="{{ $loopCount }}"
                                />
                                <span class="w-16 mr-4">{{ __($this->arqStructure[$loopCount][0]) }}</span>
                                <span class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][1]) }}</span>
                                <span class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][2]) }}</span>
                                <span class="">{{ __($this->arqStructure[$loopCount][3]) }}</span>
                                <div class="hidden ml-auto">
                                    <x-icon.checkmark/>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
