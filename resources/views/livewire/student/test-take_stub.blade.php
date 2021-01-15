<div class="w-full">
    <div class="flex flex-col py-4 space-y-10">
        <button wire:click="$set('onOverviewPage', {{ !$onOverviewPage }})" type="button"
                class="button secondary-button max-w-min">
            overview
        </button>
    <button wire:click="notification()" type="button"
                class="button primary-button max-w-min">
            send notification
        </button>

        <x-partials.question-indicator :questions="$testQuestions"></x-partials.question-indicator>
        <div class="question-container">
            <div class="p-8 sm:p-10 content-section">
                <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                    <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                        <span class="align-middle">{{ $mainQuestion->id }}</span>
                    </div>
                    <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
                    <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                    {{--        @if($mainQuestion->status = 'complete')--}}
                    {{--            <div class="ml-auto cta-primary">--}}
                    {{--                <x-icon.checkmark-small/>--}}
                    {{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
                    {{--            </div>--}}
                    {{--        @elseif($mainQuestion->status = 'pending')--}}
                    {{--            <div class="ml-auto text-gray-400">--}}
                    {{--                <x-icon.close/>--}}
                    {{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
                    {{--            </div>--}}
                    {{--        @endif--}}
                </div>
                <div class="flex flex-wrap">
                    <x-input.group for="hallos" label="hallos">
                        <x-input.text name="hallos"></x-input.text>
                    </x-input.group>
                </div>
            </div>
            @if($onOverviewPage)
                <button class="button cta-button float-right mt-3">Antwoord inleveren</button>
            @endif
        </div>

        @if($onOverviewPage)
            <div class="question-container">
                <div class="p-8 sm:p-10 content-section">
                    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                            <span class="align-middle">{{ $mainQuestion->id }}</span>
                        </div>
                        <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
                        <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                        {{--        @if($mainQuestion->status = 'complete')--}}
                        {{--            <div class="ml-auto cta-primary">--}}
                        {{--                <x-icon.checkmark-small/>--}}
                        {{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
                        {{--            </div>--}}
                        {{--        @elseif($mainQuestion->status = 'pending')--}}
                        {{--            <div class="ml-auto text-gray-400">--}}
                        {{--                <x-icon.close/>--}}
                        {{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                    </div>
                    <div class="flex flex-wrap">
                        <x-input.group for="hallos" label="hallos">
                            <x-input.text name="hallos"></x-input.text>
                        </x-input.group>
                    </div>
                </div>

                <button class="button cta-button float-right mt-3">Antwoord inleveren</button>
            </div>
            <div class="question-container">
                <div class="p-8 sm:p-10 content-section">
                    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                            <span class="align-middle">{{ $mainQuestion->id }}</span>
                        </div>
                        <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
                        <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                        {{--        @if($mainQuestion->status = 'complete')--}}
                        {{--            <div class="ml-auto cta-primary">--}}
                        {{--                <x-icon.checkmark-small/>--}}
                        {{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
                        {{--            </div>--}}
                        {{--        @elseif($mainQuestion->status = 'pending')--}}
                        {{--            <div class="ml-auto text-gray-400">--}}
                        {{--                <x-icon.close/>--}}
                        {{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                    </div>
                    <div class="flex flex-wrap">
                        <x-input.group for="hallos" label="hallos">
                            <x-input.text name="hallos"></x-input.text>
                        </x-input.group>
                    </div>
                </div>

                <button class="button cta-button float-right mt-3">Antwoord inleveren</button>
            </div>
            <div class="question-container">
                <div class="p-8 sm:p-10 content-section">
                    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                            <span class="align-middle">{{ $mainQuestion->id }}</span>
                        </div>
                        <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
                        <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                        {{--        @if($mainQuestion->status = 'complete')--}}
                        {{--            <div class="ml-auto cta-primary">--}}
                        {{--                <x-icon.checkmark-small/>--}}
                        {{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
                        {{--            </div>--}}
                        {{--        @elseif($mainQuestion->status = 'pending')--}}
                        {{--            <div class="ml-auto text-gray-400">--}}
                        {{--                <x-icon.close/>--}}
                        {{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                    </div>
                    <div class="flex flex-wrap">
                        <x-input.group for="hallos" label="hallos">
                            <x-input.text name="hallos"></x-input.text>
                        </x-input.group>
                    </div>
                </div>

                <button class="button cta-button float-right mt-3">Antwoord inleveren</button>
            </div>
            <div class="question-container">
                <div class="p-8 sm:p-10 content-section">
                    <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                        <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                            <span class="align-middle">{{ $mainQuestion->id }}</span>
                        </div>
                        <h1 class="inline-block ml-2 mr-6">{{ $mainQuestion->type }}</h1>
                        <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                        {{--        @if($mainQuestion->status = 'complete')--}}
                        {{--            <div class="ml-auto cta-primary">--}}
                        {{--                <x-icon.checkmark-small/>--}}
                        {{--                <span class="ml-auto note bold align-middle">BEANTWOORD</span>--}}
                        {{--            </div>--}}
                        {{--        @elseif($mainQuestion->status = 'pending')--}}
                        {{--            <div class="ml-auto text-gray-400">--}}
                        {{--                <x-icon.close/>--}}
                        {{--                <span class="ml-auto note bold align-middle">NIET BEANTWOORD </span>--}}
                        {{--            </div>--}}
                        {{--        @endif--}}
                    </div>
                    <div class="flex flex-wrap">
                        <x-input.group for="hallos" label="hallos">
                            <x-input.text name="hallos"></x-input.text>
                        </x-input.group>
                    </div>
                </div>

                <button class="button cta-button float-right mt-3">Antwoord inleveren</button>
            </div>

        @endif


    </div>

    {{--        <x-modal maxWidth="600" id="testModal">--}}
    {{--            <div>--}}
    {{--                <div class="title px-10 pt-6 pb-2.5">--}}
    {{--                    <h2>Let op! Vraaggroep sluit</h2>--}}
    {{--                </div>--}}
    {{--                <div class="divider mx-7"></div>--}}

    {{--                <div class="px-10 py-5 flex flex-wrap">--}}
    {{--                    <p class="body1">Door naar deze vraag te gaan, sluit je de groep vragen af waar je nu mee bezig bent. Je--}}
    {{--                        kan--}}
    {{--                        hierna niet meer terugkeren.</p>--}}
    {{--                    <div class="inline-flex pt-6 items-center space-x-6 ml-auto">--}}
    {{--                        <div @click="open = false">--}}
    {{--                            <x-button.text-button @click="open = false" rotateIcon="180">--}}
    {{--                                <x-icon.chevron/>--}}
    {{--                                <span>Terug</span>--}}
    {{--                            </x-button.text-button>--}}
    {{--                        </div>--}}
    {{--                        <x-button.primary size="md"><span>Doorgaan</span>--}}
    {{--                            <x-icon.arrow/>--}}
    {{--                        </x-button.primary>--}}
    {{--                    </div>--}}
    {{--                </div>--}}
    {{--            </div>--}}
    {{--        </x-modal>--}}
</div>
