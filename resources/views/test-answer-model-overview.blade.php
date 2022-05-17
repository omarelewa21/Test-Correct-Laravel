<x-layouts.base>
    <div class="w-full flex flex-col mb-5 overview"
         x-data="{marginTop: 0}"
         x-on:unload="(function () {window.scrollTo(0, 0);})"
         x-cloak
    >
        <div class="fixed left-0 w-full px-8 xl:px-28 flex-col pt-4 z-10 bg-light-grey" id="overviewQuestionNav">
            <div>

            </div>

            <div class="nav-overflow left-0 fixed w-full h-12"></div>
        </div>
        <div x-data="{showMe: true}"
             x-show="showMe"
             x-on:force-taken-away-blur.window="showMe = !$event.detail.shouldBlur;"
             class="w-full space-y-8 mt-40" :style="calculateMarginTop()">
            <h1 class="mb-7">{{ __('test.answer_model') }}</h1>
            @push('styling')
                <style>
                    {!! $styling !!}
                </style>
            @endpush
            @foreach($data as  $key => $testQuestion)
                <div class="flex flex-col space-y-4">
                    @if($testQuestion->type === 'OpenQuestion')
                        <livewire:answer-model.open-question
                                :question="$testQuestion"
                                :number="++$key"
                                :answers="$answers"
                                wire:key="'q-'.$testQuestion->uuid'q-'"
                        />

                    @endif
                </div>
            @endforeach
        </div>
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
</x-layouts.base>

