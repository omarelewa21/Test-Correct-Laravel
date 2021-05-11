<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col w-full">
        <div x-data="{opened: @entangle('drawingModalOpened'), answered: @entangle('answer')}"
             class="relative">

            <div class="flex flex-col space-y-3">
                <div questionHtml wire:ignore>{!! $question->getQuestionHtml() !!}</div>
                <x-button.secondary class="max-w-max" @click="opened = true" x-on:click="document.getElementById('body').classList.add('modal-open');window.scrollTo(0,0);">
                    <x-icon.edit/>
                    @if($answer == '')
                        <span>{{ __('test_take.draw_answer') }}</span>
                    @else
                        <span>{{ __('test_take.adjust_answer') }}</span>
                    @endif
                </x-button.secondary>
            </div>

            <div x-show="answered" class="mt-3">
                @if($answer != '')
                    <img id="drawnImage" class="border border-blue-grey rounded-10" width="400"
                         src="{{ route('student.drawing-question-answer',$answer, false) }}?{!! date('Ymdsi') !!}"
                         alt="">
                @endif

                <span>{{ $additionalText }}</span>
            </div>

            <div wire:ignore
                 class="fixed flex-col left-0 top-0 xl:top-10 xl:left-10 z-50 p-4 bg-white border border-blue-grey rounded-10 w-full xl:w-11/12 2xl:w-4/5 overflow-auto h-full lg:h-auto"
                 x-show.transition="opened">
                @include('components.question.drawing-modal', ['drawing' => $question->answer])
            </div>

        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :answerId="$answerId"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
