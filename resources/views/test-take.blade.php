<x-layouts.app>
    <div class="w-full flex flex-col mb-5"  selid="testtake-layout">
        @if($testParticipant->intense)
            <livewire:student.intense-observer :deviceId="$testParticipant->user_id" :sessionId="$testParticipant->id"></livewire:student.intense-observer>
        @endif
        <livewire:question.navigation  :nav="$nav" :testTakeUuid="$uuid"/>
        <div class="rs_readable">
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div selid="testtake-question">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:question.multiple-select-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:question.multiple-choice-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:question.open-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        <livewire:question.matching-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:question.completion-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:question.ranking-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:question.info-screen-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:question.drawing-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:question.matrix-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
        <x-slot name="footerbuttons">
            <div class="Rectangle">
                <svg width="17" height="16" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
                    <g stroke="#004DF5" fill="none" fill-rule="evenodd">
                        <path d="M12 15a4 4 0 0 0 4-4V8a4 4 0 0 0-4-4H7.75L4 1v3.126C2.275 4.57 1 6.136 1 8v3a4 4 0 0 0 4 4h7z" stroke-width="2" stroke-linejoin="round"/>
                        <g stroke-linecap="round">
                            <path d="M4.5 8.5v2M8.5 8.5v2M12.5 8.5v2M6.5 7.5v4M10.5 7.75v3.5"/>
                        </g>
                    </g>
                </svg>

                <span class="Lees-voor">
                  Lees voor
                </span>
            </div>
            @if(Auth::user()->text2speech)
                <div id="readspeaker_button1" wire:ignore class="rs_skip rsbtn rs_preserve ">
                    <a rel="nofollow" class="rsbtn_play" accesskey="L" title="{{ __('test_take.speak') }}" href="//app-eu.readspeaker.com/cgi-bin/rsent?customerid=12749&amp;lang=nl_nl&amp;readclass=rs_readable">
                        <span class="rsbtn_left rsimg rspart"><x-icon.audio/><span class="rsbtn_text"><span class="rsbtn_label">{{ __('test_take.speak') }}</span></span></span>
                        <span class="rsbtn_right rsimg rsplay rspart"></span>
                    </a>
                </div>
            @endif
            <div x-cloak x-data="{display :footerButtonData({{ $current }}, {{$nav->count()}})}" @update-footer-navigation.window="display= $event.detail.data" class="space-x-3">
                <x-button.text-button x-show="display.prev"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                        href="#" rotateIcon="180">
                    <x-icon.chevron/>
                    <span>{{ __('test_take.previous_question') }}</span>
                </x-button.text-button>
                <x-button.cta x-show="display.turnin"
                        size="sm"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('toOverview', {{ $nav->count() }})"
                        @click="$dispatch('show-loader')"
                >
                    <span>{{ __('test_take.overview') }}</span>
                </x-button.cta>
                <x-button.primary x-show="display.next"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')"
                        size="sm">
                    <span>{{ __('test_take.next_question') }}</span>
                    <x-icon.chevron/>
                </x-button.primary>
            </div>
        </x-slot>
        <x-slot name="testTakeManager">
            <livewire:student.test-take :testTakeUuid="$uuid" :testParticipantId="$testParticipant->getKey()" :testParticipantUuid="$testParticipant->uuid"/>
        </x-slot>
        <x-slot name="fraudDetection">
            <livewire:student.fraud-detection :testParticipantId="$testParticipant->getKey()" :testParticipantUuid="$testParticipant->uuid" :testTakeUuid="$uuid"/>
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
            } else if(q === last) {
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

