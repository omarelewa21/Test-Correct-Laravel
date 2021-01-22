<style>
    input[type="radio"]:checked {
        color:red;
    }
</style>

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
                    <label class="flex p-5 border border-blue-grey rounded-10 base multiple-choice-question transition ease-in-out duration-150 focus:outline-none @if($link->answer == 1) active @endif"
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
                        <div class="@if($link->answer != 1) hidden @endif ml-auto">
                            <x-icon.checkmark/>
                        </div>
                    </label>
                @endforeach
            </div>
        </div>
    </div>
</div>
