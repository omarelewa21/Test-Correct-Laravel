<div class="flex flex-col p-8 sm:p-10 content-section">
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


    <div class="flex flex-1 flex-col space-y-2">
        <span>Maak een tekening vraag. Bekijk ook de bijlagen bij deze vraag. Open het notitieblok om aantekeningen te noteren.</span>
            <div class="mt-3 flex flex-1 w-1/2">
        @if($answer != '')
                <img id="drawnImage" class="border border-blue-grey rounded-10" width="400" src="{{ route('student.drawing-question-answer',$answer) }}?{!! date('Ymdsi') !!}" alt="">
        @else
            <div class="flex flex-1 border border-blue-grey rounded-10 justify-center items-center">
                <span class="bold mid-grey font-size-18">Geen afbeelding</span>
            </div>
        @endif
            </div>
    </div>
</div>
