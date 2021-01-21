<div class="w-full flex flex-col mb-5" test-take-player >
    <div class="flex flex-col pt-4 pb-8 space-y-10">
        <x-partials.question-indicator :questions="$testQuestions"></x-partials.question-indicator>
    </div>
    <div class="flex flex-col p-8 sm:p-10 content-section">
        <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
            <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                <span class="align-middle">{{ $number }}</span>
            </div>
            <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->caption }}</h1>
            <h4 class="inline-block">{!!  $mainQuestion->score ? $mainQuestion->score.' pt' :'' !!}</h4>
        </div>

        <div class="flex flex-1">
            @if($mainQuestion->type === 'MultipleChoiceQuestion' && $mainQuestion->selectable_answers > 1)
                <livewire:question.multiple-select-question :uuid="$question"  wire:key="'q-'.$question"/>
            @elseif($mainQuestion->type === 'MultipleChoiceQuestion')
                <livewire:question.multiple-choice-question :uuid="$question"  wire:key="'q-'.$question"/>
            @elseif($mainQuestion->type === 'OpenQuestion')
                <livewire:question.open-question :uuid="$question" wire:key="'q-'.$question" />
            @elseif($mainQuestion->type === 'MatchingQuestion')
                <livewire:question.matching-question :uuid="$question" wire:key="'q-'.$question" />
            @elseif($mainQuestion->type === 'CompletionQuestion')
                <livewire:question.completion-question :uuid="$question" wire:key="'q-'.$question" />
            @elseif($mainQuestion->type === 'RankingQuestion')
                <livewire:question.ranking-question :uuid="$question" wire:key="'q-'.$question"/>
            @elseif($mainQuestion->type === 'InfoscreenQuestion')
                <livewire:question.info-screen-question :uuid="$question" wire:key="'q-'.$question"/>
            @endif
        </div>
    </div>
    <x-slot name="footerbuttons">
        <x-button.text-button onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"  href="#"  rotateIcon="180"><x-icon.chevron/><span>{{ __('test_take.previous_question') }}</span></x-button.text-button>
        <x-button.cta size="sm"><span>{{ __('test_take.turn_in') }}</span><x-icon.arrow/></x-button.cta>
        <x-button.primary onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')" size="sm"><span>{{ __('test_take.next_question') }}</span><x-icon.chevron/></x-button.primary>
    </x-slot>

</div>
