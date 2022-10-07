@extends($preview ?? 'livewire.teacher.questions.cms-layout')
@section('question-cms-question')
    <x-input.rich-textarea
            wire:model.debounce.1000ms="question.question"
            editorId="{{ $questionEditorId }}"
            type="cms"
            :disabled="isset($preview)"
            lang="{{ $lang }}"
            :allowWsc="$allowWsc"
    />
@endsection

@section('question-cms-answer')
    <div id="drawing-question-tool-container"
            x-data="drawingTool(
                '{{ $this->drawingToolName() }}',
                 {
                     answerSvg: @entangle('question.answer_svg'),
                     questionSvg: @entangle('question.question_svg'),
                     gridSvg: @entangle('question.grid_svg'),
                     grid: @entangle('question.grid'),
                     isOldDrawing: @entangle('isOldDrawing')
            },
            true
        )"
         @close-drawing-tool="show = false"
    >
        <div class="flex justify-between">
            <span class="flex">{{ __('cms.Teken in de tekentool het antwoordmodel voor de vraag.') }}</span>
            @isset($preview)
                <x-button.primary x-cloak x-show="answerSvg !== ''" selid="draw-answer" disabled>
                    <x-icon.edit/>
                    <span>{{ __('cms.Tekening aanpassen') }}</span>
                </x-button.primary>
            @else
                @if($isOldDrawing)
                    <x-button.primary wire:loading.attr="disabled" wire:target="handleUpdateDrawingData" x-cloak @click="showWarning = !showWarning; clearSlate = false" selid="draw-answer">
                        <x-icon.edit/>
                        <span>{{ __('cms.Tekening aanpassen') }}</span>
                    </x-button.primary>
                @else
                    <x-button.primary wire:loading.attr="disabled" wire:target="handleUpdateDrawingData" x-cloak x-show="answerSvg !== ''" @click="show = !show" selid="draw-answer">
                        <x-icon.edit/>
                        <span>{{ __('cms.Tekening aanpassen') }}</span>
                    </x-button.primary>
                @endif
            @endisset
        </div>

        <div class="flex flex-1 min-h-[500px] w-full border border-bluegrey rounded-10 mt-4 items-center justify-center relative overflow-auto drawing-tool-preview">

            @if($isOldDrawing)
                <div>
                    <div class="absolute top-0 left-0 w-full h-full">
                        <img class="object-cover" src="{{ $this->question['answer'] }}" alt="">
                    </div>

                    <div class="max-w-2xl z-0  p-8 flex flex-col items-center justify-center relative rounded-10 overflow-auto">
                        <div class="absolute bg-white opacity-80 w-full h-full"></div>
                        <div class="z-0 flex flex-col items-center justify-center">
                            @isset($preview)
                            <x-button.primary class="disabled" disabled selid="draw-answer">
                                <x-icon.edit/>
                                <span>{{ __('cms.Tekening maken') }}</span>
                            </x-button.primary>
                            @else
                                <x-button.primary @click="showWarning = !showWarning; clearSlate = true" selid="draw-answer">
                                    <x-icon.edit/>
                                    <span>{{ __('cms.Tekening maken') }}</span>
                                </x-button.primary>
                            @endisset
                            {{-- <p class="text-note text-sm text-center mt-4">{{ __('cms.waarschuwing_aanpassen_oude_tekenvraag') }} </p> --}}
                        </div>
                    </div>
                </div>
            @else
                <div class="absolute top-0 left-0 w-full h-full">
                    <svg viewBox="{{ $this->cmsPropertyBag['viewBox'] ?? '0 0 0 0' }}"
                         @viewbox-changed.window="makeGridIfNecessary(window[toolName])"
                         id="preview-svg"
                         class="w-full h-full"
                         xmlns="http://www.w3.org/2000/svg"
                         style="--cursor-type-locked:var(--cursor-crosshair); --cursor-type-draggable:var(--cursor-crosshair);">
                        <g wire:ignore id="grid-preview-svg" stroke="var(--all-BlueGrey)" stroke-width="1"></g>
                        <g class="question-svg" x-html="atob(questionSvg)"></g>
                        <g class="answer-svg" x-html="atob(answerSvg)"></g>
                    </svg>

                    {{-- extra div overlay so the svg is not hoverable--}}
                    <div class="w-full h-full absolute top-0"></div>
                </div>

                <div x-cloak x-show="answerSvg === ''" class="max-w-2xl z-0  p-8 flex flex-col items-center justify-center relative rounded-10 overflow-auto">
                    <div class="absolute bg-white opacity-80 w-full h-full"></div>
                    <div class="z-0 flex flex-col items-center justify-center">
                        @isset($preview)
                        <x-button.primary disabled selid="draw-answer">
                            <x-icon.edit/>
                            <span>{{ __('cms.Tekening maken') }}</span>
                        </x-button.primary>
                        @else
                            <x-button.primary @click="show = !show" wire:loading.attr="disabled" wire:target="handleUpdateDrawingData" selid="draw-answer">
                                <x-icon.edit/>
                                <span>{{ __('cms.Tekening maken') }}</span>
                            </x-button.primary>
                        @endif
                    </div>
                </div>
            @endif

        </div>
        @if($isOldDrawing && !isset($preview))
            <x-modal.question-editor-old-drawing-override/>
        @endif
        <x-modal.question-editor-drawing-modal/>
    </div>
@endsection
