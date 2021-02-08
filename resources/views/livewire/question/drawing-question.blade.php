<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col">
        <div x-data="{opened: @entangle('drawingModalOpened')}">
            <div class="flex flex-col space-y-3">
                <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
                <x-button.secondary class="max-w-max" @click="opened = true">
                    <x-icon.edit/>
                    <span>Antwoord tekenen</span>
                </x-button.secondary>
            </div>

            <div class="absolute flex-col left-0 -top-1/3 z-10 p-4 bg-white border border-blue-grey rounded-10 " x-show.transition="opened" style="width: 1250px;">
                <div class="flex justify-end">
                    <x-button.text-button @click="opened = false;">
                        <x-icon.close/>
                    </x-button.text-button>
                </div>
                <div class="w-full ">
                    <iframe width="100%" height="580" src="{{ route('student.show-drawing-canvas', $question->uuid) }}" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <x-attachment.attachment-modal :attachment="$attachment"/>
    <x-question.notepad :showNotepad="$showNotepad"/>
</x-partials.question-container>
