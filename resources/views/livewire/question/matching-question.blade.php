<x-partials.question-container :number="$number" :q="$q" :question="$question">

<div class="w-full space-y-3">
            <div>
                <span>{!! __('test_take.instruction_matching_question') !!}</span>
            </div>
            <div>
                {!!   $question->getQuestionHtml() !!}
            </div>
            <div class="flex flex-col" wire:sortable="updateGroupOrder" wire:sortable-group="updateOrder">
                <div class="flex">
                    <x-dropzone wire:key="group-start" title="start-zone">
                        <div wire:sortable-group.item-group="startGroep">
                            @foreach($question->matchingQuestionAnswers as $option)
                                @if(  $option->correct_answer_id !== null )
                                    <x-drag-item wire:key="option-{{ $option->id }}" sortableHandle="false"
                                                 wire:sortable-group.item="{{ $option->id }}">
                                        {{ $option->answer }}
                                    </x-drag-item>
                                @endif
                            @endforeach
                        </div>
                    </x-dropzone>
                </div>
                <div class="flex space-x-5">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <x-dropzone title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div class="min-h-full flex flex-col" wire:sortable-group.item-group="{{ $group->id }}">

                                </div>
                            </x-dropzone>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
</x-partials.question-container>
