<x-layouts.app>
    <div class="w-full flex flex-col mb-5" test-take-player x-data="{current : '{{ $current }}'}">
        <div class="flex flex-col pt-4 pb-8 space-y-10">
            <x-partials.question-indicator :questions="$data"></x-partials.question-indicator>
        </div>
        @foreach($data as  $key => $testQuestion)
            @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1)
                <livewire:question.multiple-select-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'"
                />
            @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                <livewire:question.multiple-choice-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'"
                />
            @elseif($testQuestion->type === 'OpenQuestion')
                <livewire:question.open-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'q-'"
                />
            @elseif($testQuestion->type === 'MatchingQuestion')
                <livewire:question.matching-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'"
                />
            @elseif($testQuestion->type === 'CompletionQuestion')
                <livewire:question.completion-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'"
                />
            @elseif($testQuestion->type === 'RankingQuestion')
                <livewire:question.ranking-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid'"
                />
            @elseif($testQuestion->type === 'InfoscreenQuestion')
                <livewire:question.info-screen-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid"
                />
            @elseif($testQuestion->type === 'DrawingQuestion')
                <livewire:question.drawing-question
                    :question="$testQuestion"
                    :number="++$key"
                    wire:key="'q-'.$testQuestion->uuid"
                />
            @endif
        @endforeach


        <x-slot name="footerbuttons">
            <x-button.text-button
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                href="#" rotateIcon="180">
                <x-icon.chevron/>
                <span>{{ __('test_take.previous_question') }}</span></x-button.text-button>
            <x-button.cta size="sm"><span>{{ __('test_take.turn_in') }}</span>
                <x-icon.arrow/>
            </x-button.cta>
            <x-button.primary
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')"
                size="sm"><span>{{ __('test_take.next_question') }}</span>
                <x-icon.chevron/>
            </x-button.primary>
        </x-slot>
    </div>
</x-layouts.app>

