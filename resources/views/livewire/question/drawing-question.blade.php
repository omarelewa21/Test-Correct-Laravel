<x-partials.question-container :number="$number" :q="$q" :question="$question">
    <div class="w-full space-y-3">
        <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
        <x-button.secondary class="max-w-max">
            <x-icon.edit/>
            <span>Antwoord tekenen</span>
        </x-button.secondary>

    </div>
</x-partials.question-container>
