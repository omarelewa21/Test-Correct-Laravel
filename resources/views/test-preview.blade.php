<x-layouts.app>
    <div class="w-full flex flex-col mb-5">
        <livewire:student-player.preview.navigation :nav="$nav" :testId="$testId"></livewire:student-player.preview.navigation>
        <div>
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div class="">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:student-player.preview.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:student-player.preview.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:student-player.preview.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        @php $componentName = sprintf('student-player.preview.matching-question%s', strtolower($testQuestion->subtype) === 'classify' ? '-classify' : '') @endphp
                        <livewire:is :component="$componentName"
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:student-player.preview.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:student-player.preview.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:student-player.preview.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:student-player.preview.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:student-player.preview.matrix-question
                                :question="$testQuestion"
                                :number="++$key"
                                :testId="$testId"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
        <x-slot name="footerbuttons">
            <div x-cloak x-data="{display :footerButtonData({{ $current }}, {{count($nav)}})}"
                 @update-footer-navigation.window="display= $event.detail.buttons.data" class="space-x-3">
                <x-button.text-button x-show="display.prev"
                                      onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                                      href="#" rotateIcon="180">
                    <x-icon.chevron/>
                    <span>{{ __('test_take.previous_question') }}</span>
                </x-button.text-button>
                <x-button.primary x-show="display.next"
                                  onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')"
                                  size="sm">
                    <span>{{ __('test_take.next_question') }}</span>
                    <x-icon.chevron/>
                </x-button.primary>
            </div>
        </x-slot>
        <x-slot name="testTakeManager">

        </x-slot>
        <x-slot name="fraudDetection">

        </x-slot>
    </div>
    @push('scripts')
        <script>
            function footerButtonData(q, last) {
                if (q === 1) {
                    if (q === last) {
                        data = {
                            prev: false,
                            next: false,
                            turnin: true
                        }
                    } else {
                        data = {
                            prev: false,
                            next: true,
                            turnin: false
                        }
                    }
                } else if (q === last) {
                    data = {
                        prev: true,
                        next: false,
                        turnin: true
                    }
                } else {
                    data = {
                        prev: true,
                        next: true,
                        turnin: false
                    }
                }
                return data;
            }
        </script>

    @endpush
</x-layouts.app>