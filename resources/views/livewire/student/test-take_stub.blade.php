<div class="w-full" test-take-player>
    <div class="flex flex-col py-4 space-y-10">

        <x-partials.question-indicator :questions="$testQuestions"></x-partials.question-indicator>
    </div>
    <div>
        <div class="question-container">
            <div class="p-8 sm:p-10 content-section">
                <div class="question-title flex flex-wrap items-center question-indicator border-bottom mb-6">
                    <div class="inline-flex question-number rounded-full text-center justify-center items-center complete">
                        <span class="align-middle">{{ $mainQuestion->id }}</span>
                    </div>

                    <h1 class="inline-block ml-2 mr-6" selid="questiontitle">{{ get_class($mainQuestion) }}
                        | {{   strip_tags($mainQuestion->getQuestionHtml()) }}</h1>
                    <h4 class="inline-block">{{$mainQuestion->score}}pt</h4>
                </div>

                {{--                Question types ifjes--}}
                <div>
                    @if($mainQuestion->type === 'OpenQuestion')
                        @if($mainQuestion->subtype === 'short')
                            {{$mainQuestion->subtype}}
                            @livewire('question.open-question', ['question' => $mainQuestion])

                        @else
                            <span>{{$mainQuestion->subtype}}</span>
                            @livewire('question.open-question', ['question' => $mainQuestion])

                        @endif
                    @endif

                    @if($mainQuestion->type === 'MatchingQuestion')
                        subtype: {{ $mainQuestion->subtype }}

                    @endif

                    @if($mainQuestion->type === 'RankingQuestion')
                        subtype: {{ $mainQuestion->subtype }}

                    @endif

                    @if($mainQuestion->type === 'CompletionQuestion')
                        @livewire('question.completion-question', ['question' => $mainQuestion])
                    @endif

                    <?php $selectable = 1 ?>
                    @if($mainQuestion->type === 'MultipleChoiceQuestion')
                        subtype: {{ $mainQuestion->subtype }}

                        @if($selectable == 1)
                            <div class="flex">
                                <fieldset>
                                    <legend class="sr-only">
                                        {{ __("Keuzes") }}
                                    </legend>

                                    <ul class="relative bg-white rounded-md">

                                        {{--                                        @foreach($answers as $key => $answer)--}}
                                        {{--                                            <li>--}}
                                        {{--                                                <div :class="{ 'border-gray-200': !(active === 0), ' border-indigo-200 z-10': active === 0 }"--}}
                                        {{--                                                     class="relative rounded-tl-md rounded-tr-md p-4 flex flex-col md:pl-4 md:pr-6 border-gray-200">--}}
                                        {{--                                                    <label class="flex items-center text-sm cursor-pointer">--}}
                                        {{--                                                        <input name="pricing_plan" type="radio"--}}
                                        {{--                                                               @click="select({{ $key}})"--}}
                                        {{--                                                               @keydown.space="select({{ $key }})"--}}
                                        {{--                                                               @keydown.arrow-up="onArrowUp({{ $key }})"--}}
                                        {{--                                                               @keydown.arrow-down="onArrowDown({{ $key }})"--}}
                                        {{--                                                               class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 cursor-pointer border-gray-300"--}}
                                        {{--                                                               aria-describedby="plan-option-pricing-0 plan-option-limit-0">--}}
                                        {{--                                                        <span class="ml-3 font-medium text-gray-900">{{ $answer }}</span>--}}
                                        {{--                                                    </label>--}}
                                        {{--                                                </div>--}}
                                        {{--                                            </li>--}}
                                        {{--                                        @endforeach--}}

                                        <li>
                                            <div class="relative p-2 flex flex-col md:pl-4 md:pr-6 border-gray-200">
                                                <label class="flex items-center text-sm cursor-pointer">
                                                    <input name="pricing_plan" type="radio" @click="select(0)"
                                                           @keydown.space="select(0)"
                                                           @keydown.arrow-up="onArrowUp(0)"
                                                           @keydown.arrow-down="onArrowDown(0)"
                                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 cursor-pointer border-gray-300"
                                                           aria-describedby="plan-option-pricing-0 plan-option-limit-0">
                                                    <span class="ml-3 font-medium text-gray-900">Startup</span>
                                                </label>
                                            </div>
                                        </li>
                                        <li>
                                            <div class="relative p-2 flex flex-col md:pl-4 md:pr-6 border-gray-200">
                                                <label class="flex items-center text-sm cursor-pointer">
                                                    <input name="pricing_plan" type="radio" @click="select(1)"
                                                           @keydown.space="select(1)"
                                                           @keydown.arrow-up="onArrowUp(1)"
                                                           @keydown.arrow-down="onArrowDown(1)"
                                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 cursor-pointer border-gray-300"
                                                           aria-describedby="plan-option-pricing-1 plan-option-limit-1"
                                                           checked="">
                                                    <span class="ml-3 font-medium text-gray-900">Business</span>
                                                </label>
                                            </div>
                                        </li>


                                        <li>
                                            <div class="relative rounded-bl-md rounded-br-md p-4 flex flex-col md:pl-4 md:pr-6 border-indigo-200 z-10">
                                                <label class="flex items-center text-sm cursor-pointer">
                                                    <input name="pricing_plan" type="radio" @click="select(2)"
                                                           @keydown.space="select(2)"
                                                           @keydown.arrow-up="onArrowUp(2)"
                                                           @keydown.arrow-down="onArrowDown(2)"
                                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 cursor-pointer border-gray-300"
                                                           aria-describedby="plan-option-pricing-2 plan-option-limit-2">
                                                    <span class="ml-3 font-medium text-gray-900">Enterprise</span>
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                </fieldset>
                            </div>
                        @endif
                        @if($selectable > 1)
                            <div class="checkbox-group space-y-3">
                                {{--                                @foreach($answers as $key => $answer)--}}
                                {{--                                    <div class="flex items-center">--}}
                                {{--                                        <input type="checkbox" name="{{ $answer }}" id="{{ $answer }}"--}}
                                {{--                                               class="form-checkbox h-5 w-5 text-blue-600">--}}
                                {{--                                        <label for="{{ $answer }}">{{ $answer }}</label>--}}
                                {{--                                    </div>--}}
                                {{--                                @endforeach--}}

                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600" checked>
                                    <label> Checkbox</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                                    <label> Checkbox</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                                    <label> Checkbox</label>
                                </div>
                                <div class="flex items-center">
                                    <input type="checkbox" class="form-checkbox h-5 w-5 text-blue-600">
                                    <label> Checkbox</label>
                                </div>
                            </div>
                        @endif
                    @endif

                </div>

            </div>
        </div>
    </div>

    <x-slot name="footerbuttons">
        <x-button.text-button wire:offline.attr="disabled"
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('previousQuestion')"
                href="#" rotateIcon="180">
            <x-icon.chevron/>
            <span>{{ __("test-take_stub.Vorige vraag") }}</span>
        </x-button.text-button>

        <x-button.cta wire:offline.attr="disabled"
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('showModal')"
                size="sm"><span>{{ __("test-take_stub.Inleveren") }}</span>
            <x-icon.arrow/>
        </x-button.cta>

        <x-button.primary wire:offline.attr="disabled"
                onclick="livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).call('nextQuestion')"
                size="sm"><span>{{ __("test-take_stub.Volgende vraag") }}</span>
            <x-icon.chevron/>
        </x-button.primary>
    </x-slot>


        <x-modal id="modal">
            <x-slot name="title">
                <h1>{{ __("test-take_stub.Weet je zeker dat je wilt inleveren") }}?</h1>
            </x-slot>

            <x-slot name="body">
                <p>{{ __("test-take_stub.Zodra de toets is ingeleverd kun je niet meer terug") }}.</p>
            </x-slot>

            <x-slot name="footer">
                <x-button.text-button @click="open = false">
                    <x-icon.chevron/>
                    <span>{{ __("test-take_stub.Terug") }}</span></x-button.text-button>
                <x-button.primary><span>{{ __("test-take_stub.Doorgaan") }}</span>
                    <x-icon.arrow/>
                </x-button.primary>
            </x-slot>

        </x-modal>

</div>
