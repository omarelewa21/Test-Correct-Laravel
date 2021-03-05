<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col w-full">
        <div x-data="{opened: @entangle('drawingModalOpened'), answered: @entangle('answer')}"
             class="relative">

            <div class="flex flex-col space-y-3">
                <span>Maak een tekening vraag.
                    @if(!$question->attachments->isEmpty()) Bekijk ook de bijlagen bij deze vraag. @endif
                    @if($question->note_type != "NONE") Open het notitieblok om aantekeningen te noteren. @endif
                </span>
                {!! $question->getQuestionHtml() !!}
                <x-button.secondary class="max-w-max" @click="opened = true">
                    <x-icon.edit/>
                    <span>Antwoord tekenen</span>
                </x-button.secondary>
            </div>

            <div x-show="answered" class="mt-3">
                @if($answer != '')
                    <img id="drawnImage" class="border border-blue-grey rounded-10" width="400"
                         src="{{ route('student.drawing-question-answer',$answer, false) }}?{!! date('Ymdsi') !!}" alt="">

                @endif

                <span>{{ $additionalText }}</span>
            </div>

            <div wire:ignore
                 class="fixed flex-col top-0 left-0 z-50 p-4 bg-white border border-blue-grey rounded-10 "
                 x-show.transition="opened" style="width: 1250px;">
                @include('components.question.drawing-modal', ['drawing' => $question->answer])
            </div>

        </div>
    </div>
    <x-attachment.attachment-modal :attachment="$attachment" :player-instance="$playerInstance"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
