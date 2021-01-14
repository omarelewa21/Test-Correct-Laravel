<div class="w-full">
    <div class="flex flex-col mb-12">
        <x-partials.question-indicator :questions="$testTake->test->testQuestions"></x-partials.question-indicator>


        <x-question-container :mainQuestion="$mainQuestion->question">

            {{--            <livewire:question.open-question :question="$mainQuestion->question" :key="$mainQuestion->uuid"/>--}}

        </x-question-container>
        <div class="ml-auto mt-5 inline-flex">
            <x-button.cta>Antwoord bewerken</x-button.cta>
        </div>
    </div>

    {{--    <x-modal.dialog wire:model="showModal" maxWidth="lg" id="modal">--}}
    {{--        <x-slot name="title">--}}
    {{--            <div class="title px-10 pt-6 pb-2.5">--}}
    {{--                <h2>Let op! Vraaggroep sluit</h2>--}}
    {{--            </div>--}}

    {{--            <div class="divider mx-7"></div>--}}
    {{--        </x-slot>--}}
    {{--        <x-slot name="content">--}}
    {{--            <p class="body1">Door naar deze vraag te gaan, sluit je de groep vragen af waar je nu mee bezig bent. Je kan--}}
    {{--                hierna niet meer terugkeren.</p>--}}
    {{--        </x-slot>--}}
    {{--        <x-slot name="footer">--}}
    {{--            <div class="px-10 py-5 flex flex-wrap">--}}
    {{--                <div class="inline-flex pt-6 items-center space-x-6 ml-auto">--}}
    {{--                    <x-button.text-button rotateIcon="180">--}}
    {{--                        <x-icon.chevron/>--}}
    {{--                        <span>Terug</span>--}}
    {{--                    </x-button.text-button>--}}
    {{--                    <x-button.primary size="md"><span>Doorgaan</span>--}}
    {{--                        <x-icon.arrow/>--}}
    {{--                    </x-button.primary>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </x-slot>--}}
    {{--    </x-modal.dialog>--}}
</div>
</div>