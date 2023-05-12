<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-1">
        <div class="w-full space-y-3">
            <div>
                <span>{!! __('test_take.instruction_arq') !!}</span>
            </div>
            <div class="flex flex-row space-x-5">
                <div class="flex-1 space-y-6" questionHtml wire:ignore>
                    {!! $question->converted_question_html !!}
                </div>
                <div class="flex flex-1 flex-col">
                    <div>
                        <div class="px-5 space-x-4 text-base bold flex flex-row">
                            <span class="w-16">{{__('test_take.option')}}</span>
                            <span class="w-20">{{__('test_take.thesis')}} 1</span>
                            <span class="w-20">{{__('test_take.thesis')}} 2</span>
                            <span class="w-10">{{__('test_take.reason')}}</span>
                        </div>
                    </div>
                    <div class="divider my-2"></div>
                    <div class="space-y-2">
                        @foreach($question->multipleChoiceQuestionAnswers as $loopCount => $link)
                            <label id="arq_{{$link->id}}" wire:key="arq_{{$link->id}}"
                                class="flex p-5 border-2 border-blue-grey rounded-10 base multiple-choice-question
                                transition ease-in-out duration-150 {!! ($this->answer == $link->id) ? 'active' :'' !!}
                                        focus:outline-none"
                                for="link{{ $link->id }}">
                                <input
                                    wire:model="answer"
                                    id="link{{ $link->id }}"
                                    name="Question_{{ $question->id }}"
                                    type="radio"
                                    class="hidden"
                                    value="{{ $link->id }}"

                                >
                                <span id="arq_option_1{{$loopCount}}_{{$link->id}}" wire:key="arq_option_1{{$loopCount}}_{{$link->id}}" class="w-16 mr-4">{{ __($this->arqStructure[$loopCount][0]) }}</span>
                                <span id="arq_option_2{{$loopCount}}_{{$link->id}}" wire:key="arq_option_2{{$loopCount}}_{{$link->id}}" class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][1]) }}</span>
                                <span id="arq_option_3{{$loopCount}}_{{$link->id}}" wire:key="arq_option_3{{$loopCount}}_{{$link->id}}" class="w-20 mr-4">{{ __($this->arqStructure[$loopCount][2]) }}</span>
                                <span id="arq_option_4{{$loopCount}}_{{$link->id}}" wire:key="arq_option_4{{$loopCount}}_{{$link->id}}" class="">{{ __($this->arqStructure[$loopCount][3]) }}</span>
                                <div id="arq_selected_{{$loopCount}}_{{$link->id}}" wire:key="arq_selected_{{$loopCount}}_{{$link->id}}" class="ml-auto   {!! ($this->answer == $link->id) ? '' :'hidden' !!}">
                                    <x-icon.checkmark/>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad" />
</x-partials.question-container>

