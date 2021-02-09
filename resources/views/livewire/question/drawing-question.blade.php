<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col">
        <div x-data="{opened: @entangle('drawingModalOpened')}" class="relative">
            <div class="flex flex-col space-y-3">
                <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
                <x-button.secondary class="max-w-max" @click="opened = true">
                    <x-icon.edit/>
                    <span>Antwoord tekenen</span>
                </x-button.secondary>
            </div>
            @if($answer != '')
                <div class="mt-3">
                    <img id="drawnImage" class="border border-blue-grey rounded-10" width="400" src="{{ $answer }}?{!! date('Ymdsi') !!}" alt="">
                </div>
            @endif
            <div wire:ignore class="fixed flex-col top-0 left-0 z-50 p-4 bg-white border border-blue-grey rounded-10 "
                 x-show.transition="opened" style="width: 1250px;">
                @include('components.question.drawing-modal', ['drawing' => $question->answer])
            </div>
        </div>
    </div>

    <x-attachment.attachment-modal :attachment="$attachment"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
