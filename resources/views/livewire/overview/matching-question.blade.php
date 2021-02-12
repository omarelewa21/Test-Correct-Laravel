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

        <div class="w-full space-y-3 matching-question">
            <div>
                <span>{!! __('test_take.instruction_matching_question') !!}</span>
            </div>
            <div>
                {!!   $question->getQuestionHtml() !!}
            </div>
            @if($question->subtype == 'Classify')
                <div class="flex flex-col" >
                    <div class="flex">
                        <x-dropzone wire:key="group-start" startGroup="true">
                            <div class="h-full space-x-1 focus:outline-none">
                                @foreach($question->matchingQuestionAnswers as $option)
                                    @if(  $option->correct_answer_id !== null )
                                        @if($answerStruct[$option->id] === '')
                                            <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                         wire:sortable-group.item="{{ $option->id }}">
                                                {{ $option->answer }}
                                            </x-drag-item-disabled>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </x-dropzone>
                    </div>
                    <div class="flex space-x-5 classify">
                        @foreach ($question->matchingQuestionAnswers as $group)
                            @if(  $group->correct_answer_id === null )
                                <x-dropzone type="classify" title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                            wire:sortable.item="{{ $group->id }}">
                                    <div class="flex-col w-full h-full" >
                                        @foreach($question->matchingQuestionAnswers as $option)
                                            @if(  $option->correct_answer_id !== null )
                                                @if($answerStruct[$option->id] == $group->id)
                                                    <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                                 wire:sortable-group.item="{{ $option->id }}">
                                                        {{ $option->answer }}
                                                    </x-drag-item-disabled>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                </x-dropzone>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
            @if($question->subtype == 'Matching')
                <div class="flex flex-col space-y-1" >
                    <div class="flex">
                        <x-dropzone wire:key="group-start" startGroup="true">
                            <div class="h-full space-x-1 focus:outline-none" >
                                @foreach($question->matchingQuestionAnswers as $option)
                                    @if(  $option->correct_answer_id !== null )
                                        @if($answerStruct[$option->id] === '')
                                            <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                         wire:sortable-group.item="{{ $option->id }}">
                                                {{ $option->answer }}
                                            </x-drag-item-disabled>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </x-dropzone>
                    </div>
                    <div class="flex flex-col space-y-3">
                        @foreach ($question->matchingQuestionAnswers as $group)
                            @if(  $group->correct_answer_id === null )
                                <div class="flex space-x-2">
                                    <div class="w-1/3">
                                    <span class="flex w-full py-2 px-4 border-2 border-blue-grey rounded-10
                                                 bg-primary-light font-size-18 bold base leading-5">
                                                {{ $group->answer }}
                                    </span>
                                    </div>
                                    <div class="flex-1">
                                        <x-dropzone type="matching" wire:key="group-{{ $group->id }}"
                                                    wire:sortable.item="{{ $group->id }}">
                                            <div class="flex w-full h-full" >
                                                @foreach($question->matchingQuestionAnswers as $option)
                                                    @if(  $option->correct_answer_id !== null )
                                                        @if($answerStruct[$option->id] == $group->id)
                                                            <x-drag-item-disabled wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                                         wire:sortable-group.item="{{ $option->id }}">
                                                                {{ $option->answer }}
                                                            </x-drag-item-disabled>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </x-dropzone>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
