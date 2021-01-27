<x-layouts.app>
    <div class="w-full flex flex-col mb-5 overview">
        <livewire:overview.navigation :questions="$data"></livewire:overview.navigation>
        <h1 class="mb-7">Kijk alle antwoorden nog eens goed na.</h1>
        <div class="w-full space-y-8">
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1)
                        <livewire:overview.multiple-select-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:overview.multiple-choice-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:overview.open-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:overview.matching-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:overview.completion-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:overview.ranking-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:overview.info-screen-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:overview.drawing-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                    <div class="flex">
                        <x-button.primary class="ml-auto">{!!__('test_take.adjust_answer') !!}</x-button.primary>
                    </div>
                </div>
            @endforeach
        </div>


        <x-slot name="footerbuttons">
            <x-button.text-button
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                href="#" rotateIcon="180">
                <x-icon.chevron/>
                <span>{{ __('test_take.back_to_questions') }}</span></x-button.text-button>
            <x-button.cta size="sm"><span>{{ __('test_take.turn_in') }}</span>
                <x-icon.arrow/>
            </x-button.cta>
        </x-slot>
    </div>
</x-layouts.app>

