<div class="flex flex-col p-8 sm:p-10 content-section" x-data="{ showMe: false }"
     x-on:current-updated.window="showMe = ({{ $number }} == $event.detail.current)" x-show="showMe">
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
                <span>{!! __('test_take.instruction_matching_question') !!}</span>
            </div>
            <div>
                {!!   $question->getQuestionHtml() !!}
            </div>
            <div wire:sortable="updateGroupOrder" wire:sortable-group="updateOrder" style="display: flex">
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
                <div class="flex flex-col">
                    @foreach ($question->matchingQuestionAnswers as $group)
                        @if(  $group->correct_answer_id === null )
                            <x-dropzone title="{{ $group->answer }}" wire:key="group-{{ $group->id }}"
                                        wire:sortable.item="{{ $group->id }}">
                                <div class="min-h-full" wire:sortable-group.item-group="{{ $group->id }}">

                                </div>
                            </x-dropzone>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
