<x-layouts.app>

{{--   <livewire:student.test-take-offline></livewire:student.test-take-offline>--}}


    <div class="w-full flex flex-col mb-5"  selid="testtake-layout">
        @if($testParticipant->intense)
            <livewire:student.intense-observer :deviceId="$testParticipant->user_id" :sessionId="$testParticipant->id"></livewire:student.intense-observer>
        @endif
        <livewire:question.navigation  :nav="$nav" :testTakeUuid="$uuid"/>


        <div class="test-take-questions">
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
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:question.multiple-choice-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:question.open-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        @php $componentName = sprintf('question.matching-question%s', strtolower($testQuestion->subtype) === 'classify' ? '-classify' : '') @endphp
                        <livewire:is :component="$componentName"
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:question.completion-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:question.ranking-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:question.info-screen-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:question.drawing-question
                            :question="$testQuestion"
                            :number="++$key"
                            :answers="$answers"
                            :testTakeUuid="$uuid"
                            wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:question.matrix-question
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
            <div x-cloak x-data="{display :footerButtonData({{ $current }}, {{$nav->count()}})}" @update-footer-navigation.window="display= $event.detail.data" class="space-x-3">
                <x-button.text-button x-show="display.prev"
                        onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                        href="#" rotateIcon="180">
                    <x-icon.chevron/>
                    <span>{{ __('test_take.previous_question') }}</span>
                </x-button.text-button>
                <x-button.cta x-show="display.turnin"
                        id="overviewBtnFooter"
                        size="sm"
                        onclick="toOverview({{ $nav->count() }})"
                        {{-- @click="$dispatch('show-loader')" --}}
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
        function toOverview(q){
                $question = @js($data)[q-1];
                if($question['type'].toLowerCase() == 'openquestion' && $question['subtype'].toLowerCase() != 'short'){
                    setTimeout(function(){
                            livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('toOverview', q)
                        }, 500)
                }else{
                    livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('toOverview', q)
                }
            }
    </script>
        <script>
            document.addEventListener("DOMContentLoaded", () => {
                addTitleToImages('.test-take-questions',"{{__('Beschrijving afbeelding niet beschikbaar')}}");
            });
        </script>
    @endpush
</x-layouts.app>

