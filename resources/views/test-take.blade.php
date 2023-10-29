<x-layouts.app>

{{--   <livewire:student.test-take-offline></livewire:student.test-take-offline>--}}


    <div class="w-full flex flex-col mb-5"  selid="testtake-layout">
        @if($testParticipant->intense)
            <livewire:student.intense-observer :deviceId="$testParticipant->user_id" :sessionId="$testParticipant->id"></livewire:student.intense-observer>
        @endif
        <livewire:student-player.question.navigation  :nav="$nav" :testTakeUuid="$uuid"/>


        <div class="test-take-questions">
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div selid="testtake-question" class="test-take-question-{{ $key + 1 }}">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:student-player.question.multiple-select-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:student-player.question.multiple-choice-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:student-player.question.open-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        @php $componentName = sprintf('student-player.question.matching-question%s', strtolower($testQuestion->subtype) === 'classify' ? '-classify' : '') @endphp
                        <livewire:is :component="$componentName"
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:student-player.question.completion-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:student-player.question.ranking-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:student-player.question.info-screen-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:student-player.question.drawing-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:student-player.question.matrix-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif
                </div>
            @endforeach
        </div>
        <x-slot name="readspeaker">
            @if(Auth::user()->text2speech)
                <div class="Rectangle rs_clicklistenexclude rs_starter_button" onclick="ReadspeakerTlc.player.startRsPlayer()">
                    <x-icon.rs-audio/>
                    <div class="Lees-voor">
                        {{ __('test_take.speak') }}
                    </div>
                </div>
                <div id="readspeaker_button1" wire:ignore class="rs_skip rsbtn rs_preserve hidden" >
                    <a rel="nofollow" class="rsbtn_play"  title="{{ __('test_take.speak') }}" href="//app-eu.readspeaker.com/cgi-bin/rsent?customerid=12749&amp;lang=nl_nl&amp;readclass=rs_readable">
                        <span class="rsbtn_left rsimg rspart oval"><x-icon.rs-audio-inverse/></span>
                        <span class="rsbtn_right rsimg rsplay rspart"></span>
                    </a>
                </div>
            @endif
        </x-slot>
        <x-slot name="footerbuttons">
            <div x-cloak
                 x-data="{
                    display: footerButtonData({{ $current }}, {{$nav->count()}}),
                    current: {{ $current }}
                 }"
                 @update-footer-navigation.window="display = $event.detail.buttons.data; current = $event.detail.number"
                 class="space-x-3"
            >
                <x-button.text x-show="display.prev" x-on:click="$store.studentPlayer.previous(current)" href="#" rotateIcon="180">
                    <x-icon.chevron/>
                    <span>{{ __('test_take.previous_question') }}</span>
                </x-button.text>
                <x-button.cta x-show="display.turnin"
                              id="overviewBtnFooter"
                              size="sm"
                              x-on:click="$store.studentPlayer.toOverview({{ $nav->count() }})"
                >
                    <span>{{ __('test_take.overview') }}</span>
                </x-button.cta>
                <x-button.primary x-show="display.next" x-on:click="$store.studentPlayer.next(current)" size="sm">
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
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                addTitleToImages('.test-take-questions',"{{__('Beschrijving afbeelding niet beschikbaar')}}");
            });
        </script>

    @endpush
    @if($allowMrChadd)
        @pushonce('scripts')
            <script defer src="https://c.mrchadd.nl/embed.js"></script>
        @endpushonce
    @endif
</x-layouts.app>

