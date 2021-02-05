<x-partials.question-container :number="$number" :question="$question">
    <div class="flex flex-col space-y-3">
        <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
        <x-button.secondary class="max-w-max">
            <x-icon.edit/>
            <span>Antwoord tekenen</span>
        </x-button.secondary>

    </div>
    <x-attachment.attachment-modal :attachment="$attachment" />
    <x-notepad :showNotepad="$showNotepad" />
</x-partials.question-container>
