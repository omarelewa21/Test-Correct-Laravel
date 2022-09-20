<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col w-full">
        <div x-data="{opened: @entangle('drawingModalOpened'), answered: @entangle('answer')}"
             class="relative">

            <div class="flex flex-col space-y-3">
                <div wire:ignore>{!! $question->converted_question_html !!}</div>
                @if(!$usesNewDrawingTool)
                    <x-button.secondary class="max-w-max"
                                        @click="opened = true; {{ $this->playerInstance }}.rerender();"
                                        x-on:click="document.getElementById('body').classList.add('modal-open');window.scrollTo(0,0);">
                        <x-icon.edit/>
                        @if($answer == '')
                            <span>{{ __('test_take.draw_answer') }}</span>
                        @else
                            <span>{{ __('test_take.adjust_answer') }}</span>
                        @endif
                    </x-button.secondary>
                @endif
            </div>

            @if($usesNewDrawingTool)
                <div id="drawingTool{{ $this->number }}"
                     x-data="drawingTool( {{ $this->number }}, { answerSvg: @entangle('answer_svg'), questionSvg: @entangle('question_svg'), gridSvg: @entangle('grid_svg')}, false, true )"
                     @close-drawing-tool="show = false"
                     class="mt-4 preview"
                >
                    <x-button.primary @click="show = !show" selid="draw-answer">
                        <x-icon.edit/>
                        @if($answer == '')
                            <span>{{ __('test_take.draw_answer') }}</span>
                        @else
                            <span>{{ __('test_take.adjust_answer') }}</span>
                        @endif
                    </x-button.primary>
                    <x-modal.question-editor-drawing-modal/>
                </div>
            @else
                <div wire:ignore
                     id="{{ $this->playerInstance }}drawing-question-modal-container"
                     class="fixed flex-col left-0 top-0 xl:top-10 xl:left-10 z-50 p-4 bg-white border border-blue-grey rounded-10 w-full xl:w-11/12 2xl:w-4/5 overflow-auto h-full lg:h-auto"
                     x-show.transition="opened">
                    @include('components.question.drawing-modal', ['drawing' => $question->answer])
                </div>

            @endif
        </div>
    </div>
    <x-attachment.preview-attachment-modal :attachment="$attachment" :questionId="$questionId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
