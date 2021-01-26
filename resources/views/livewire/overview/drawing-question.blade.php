<div class="flex flex-col p-8 sm:p-10 content-section" >
    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
        <div class="inline-flex question-number rounded-full text-center justify-center items-center {!! $answer? 'complete': 'incomplete' !!}">
            <span class="align-middle">{{ $number }}</span>
        </div>
        <h1 class="inline-block ml-2 mr-6"> {!! __($question->caption) !!} </h1>
        <h4 class="inline-block">{{ $question->score }} pt</h4>
        @if ($this->answer)
            <x-answered></x-answered>
        @else
            <x-not-answered></x-not-answered>
        @endif
    </div>

    <div class="">
        <div class="">
            <div class="flex flex-col space-y-2">
                <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
                <x-button.secondary class="max-w-max"><x-icon.edit/><span>Antwoord tekenen</span></x-button.secondary>
            </div>
        </div>
    </div>
</div>
