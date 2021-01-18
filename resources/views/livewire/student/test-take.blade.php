<div class="w-full" test-take-player>
    <div class="flex flex-col py-4 space-y-10">
        <x-partials.question-indicator :questions="$testQuestions"></x-partials.question-indicator>
    </div>

    <div class="bg-white rounded-10 p-8 sm:p-10 content-section">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">{{ ($question + 1) }}</span>
            </div>
            <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }} | {{ $component }} {{ get_class($mainQuestion) }}</h1>
            <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
        </div>

        <div class="flex flex-wrap">
            @if($mainQuestion->type === 'MultipleChoiceQuestion')
                <livewire:question.multiple-choice-question :question="$mainQuestion->question" wire:key="2"></livewire:question.multiple-choice-question>
            @elseif($mainQuestion->type === 'OpenQuestion')
                <livewire:question.multiple-choice-question :question="$mainQuestion->question" wire:key="1"></livewire:question.multiple-choice-question>

            @endif
        </div>
    </div>
    <x-slot name="footerbuttons">
        <x-button.text-button onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"  href="#"  rotateIcon="180"><x-icon.chevron/><span>Vorige vraag</span></x-button.text-button>
        <x-button.cta size="sm"><span>Inleveren</span><x-icon.arrow/></x-button.cta>
        <x-button.primary onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')" size="sm"><span>Volgende vraag</span><x-icon.chevron/></x-button.primary>
    </x-slot>

</div>
