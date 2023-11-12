<x-layouts.app>
{{--    <livewire:student.test-take-offline></livewire:student.test-take-offline>--}}

    <div class="w-full flex flex-col mb-5 overview"
         x-data="{marginTop: 0}"
         x-on:unload="(function () {window.scrollTo(0, 0);})"
         x-cloak
    >
        <div class="fixed left-0 w-full px-8 xl:px-28 flex-col pt-4 z-10 bg-light-grey" id="overviewQuestionNav">
            <div>
                <livewire:student-player.overview.navigation :nav="$nav" :testTakeUuid="$uuid" :playerUrl="$playerUrl"/>
            </div>

            <div class="nav-overflow left-0 fixed w-full h-12"></div>
        </div>
        <div x-data="{showMe: true}"
             x-show="showMe"
             x-on:force-taken-away-blur.window="showMe = !$event.detail.shouldBlur;"
             class="w-full space-y-8 mt-40" :style="calculateMarginTop()">
            <h1 class="mb-7">{{ __('test_take.overview_review_answers') }}</h1>
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                @foreach ($groupedQuestions as $groupedQuestion)
                    @if ($groupedQuestion[0] == $testQuestion->id )
                        @foreach ($groupQuestions as $groupQuestion)
                            @if ($groupQuestion->id == $testQuestion->belongs_to_groupquestion_id)
                                    <livewire:student-player.attachments-group-preview
                                        :question="$testQuestion"
                                        :answers="$answers"
                                        wire:key="'q-'.$testQuestion->uuid'q-'"
                                    />
                            @endif
                        @endforeach
                    @endif
                @endforeach
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'MultipleChoiceQuestion' && $testQuestion->selectable_answers > 1 && $testQuestion->subtype != 'ARQ')
                        <livewire:student-player.overview.multiple-select-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'MultipleChoiceQuestion')
                        <livewire:student-player.overview.multiple-choice-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'OpenQuestion')
                        <livewire:student-player.overview.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'q-'"
                        />
                    @elseif($testQuestion->type === 'MatchingQuestion')
                        @php $componentName = sprintf('student-player.overview.matching-question%s', strtolower($testQuestion->subtype) === 'classify' ? '-classify' : '') @endphp
                        <livewire:is :component="$componentName"
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'CompletionQuestion')
                        <livewire:student-player.overview.completion-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'RankingQuestion')
                        <livewire:student-player.overview.ranking-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'"
                        />
                    @elseif($testQuestion->type === 'InfoscreenQuestion')
                        <livewire:student-player.overview.info-screen-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'DrawingQuestion')
                        <livewire:student-player.overview.drawing-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @elseif($testQuestion->type === 'MatrixQuestion')
                        <livewire:student-player.overview.matrix-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid"
                        />
                    @endif

                    @if($testQuestion->type != 'InfoscreenQuestion')
                        <div class="flex">
                            @if(!$nav[$key-1]['closed'] && !$nav[$key-1]['group']['closed'])
                                <x-button.primary onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('goToQuestion',{{ $key }})"
                                                  @click="$dispatch('show-loader')"
                                                  class="ml-auto"><span>{!!__('test_take.adjust_answer') !!}</span></x-button.primary>
                            @else
                                <span class="text-sm note w-60 ml-auto text-right">{{ __('test_take.question_closed_text_short') }}</span>
                            @endif
                        </div>
                    @endif
                </div>
                @foreach ($groupedQuestions as $groupedQuestion)
                    @if (end($groupedQuestion) == $testQuestion->id )
                        <hr style="background: var(--all-Base);">
                    @endif
                @endforeach
                @foreach ($nonGroupedQuestions as $nonGroupedQuestion)
                    @if ($nonGroupedQuestion === $testQuestion->id)
                        <hr style="background: var(--all-Base);">
                    @endif
                @endforeach
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
            <x-button.text type="link"
                                  href="{{ $playerUrl }}?q=1"
                                  rotateIcon="180"
            >
                <x-icon.chevron/>
                <span>{{ __('test_take.back_to_questions') }}</span></x-button.text>
            <x-button.cta size="sm"
                          onclick="livewire.find(document.querySelector('[testtakemanager]').getAttribute('wire:id')).call('turnInModal')">
                <span>{{ __('test_take.turn_in') }}</span>
            </x-button.cta>
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
        function calculateMarginTop() {
            var questionNav = document.getElementById('overviewQuestionNav').offsetHeight;
            var shadow = 48;
            var total = questionNav+shadow;
            return 'margin-top:' + total +'px';
        }
    </script>
    @endpush
</x-layouts.app>

