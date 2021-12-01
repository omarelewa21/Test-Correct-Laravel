<div>
    <div class="question-editor-header">
        <div class="question-title">
            <div class="icon-arrow">
                <x-icon.edit></x-icon.edit>

            </div>
            <h5 class=" text-white">{{ $questionType }}</h5>
        </div>
        <div class="question-test-name">
            <span><?= __('test') ?>:</span>
            <span class="bold">{{ $testName }}</span>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto  mt-20">

            <div class="pb-20 px-4 sm:px-6 lg:pb-8 lg:px-8">
                <div class="relative max-w-lg mx-auto divide-y-2 divide-gray-200 lg:max-w-7xl">

                    <div class="mt-12 grid gap-16 pt-12 lg:grid-cols-4 lg:gap-x-5 lg:gap-y-12">
                        <div>
                            Punten <input wire:model="question.score" style="width:50px;" verify="notempty" type="text"
                                          id="QuestionScore" spellcheck="false" autocapitalize="off" autocorrect="off"
                                          autocomplete="off"></div>
                        <div>
                            <input type="checkbox" wire:model="question.closeable" value="1" id="QuestionCloseable"
                                   spellcheck="false" autocapitalize="off" autocorrect="off" autocomplete="off"> Deze
                            vraag afsluiten <span class="fa fa-info-circle"
                                                  onclick="Popup.load('/questions/closeable_info', 500);"
                                                  style="cursor:pointer"></span><br>
                            <input type="checkbox" wire:model="question.discuss" value="1" checked="checked"
                                   id="QuestionDiscuss" spellcheck="false" autocapitalize="off" autocorrect="off"
                                   autocomplete="off"> Bespreken in de klas <br>
                            <input type="checkbox" wire:model="question.maintain_position" value="1"
                                   id="QuestionMaintainPosition" spellcheck="false" autocapitalize="off"
                                   autocorrect="off" autocomplete="off"> Deze vraag vastzetten<br>
                        </div>
                        <div>
                            <input type="checkbox" wire:model="question.decimal_score" value="1"
                                   id="QuestionDecimalScore" spellcheck="false" autocapitalize="off" autocorrect="off"
                                   autocomplete="off"> Halve punten mogelijk<br>
                            <input type="checkbox" wire:model="question.add_to_database" value="1" checked="checked"
                                   id="QuestionAddToDatabase" spellcheck="false" autocapitalize="off" autocorrect="off"
                                   autocomplete="off"> Openbaar maken <span class="fa fa-info-circle"
                                                                            onclick="Popup.load('/questions/public_info', 500);"
                                                                            style="cursor:pointer"></span><br>
                        </div>

                        <div>
                            <select wire:model="question.note_type" id="QuestionNoteType">
                                <option value="NONE">Geen kladblok</option>
                                <option value="TEXT">Tekstvlak</option>
                                <option value="DRAWING">Tekenvlak</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col flex-1" x-data="{openTab:@entangle('openTab')}">
                <div class="flex w-full space-x-6 mb-5 border-b border-light-grey">
                    <div :class="{'border-b-2 border-primary -mb-px' : openTab === 1}">
                        <x-button.text-button class="primary"
                                              @click="openTab = 1"
                        >
                            {{ __('Opstellen') }}
                        </x-button.text-button>
                    </div>
                    <div class="" :class="{'border-b-2 border-primary -mb-px' : openTab === 2}">
                        <x-button.text-button class="primary"
                                              @click="openTab = 2;"
                        >
                            {{ __('Instellingen') }}
                        </x-button.text-button>
                    </div>
                </div>


                <div class="flex flex-col flex-1 pb-20" x-show="openTab === 1">
                    <div class="content-section p-10 mb-4 space-y-5 shadow-xl flex flex-col ">
                        <div class="-ml-4 -mt-4 flex justify-between items-center flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-4">
                                <h3 class="text-lg leading-6 text-gray-900">
                                    {{ __('Vraag') }}
                                </h3>

                                <x-input.textarea wire:model="question.question"></x-input.textarea>
                                @error('question.question')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="content-section p-10 mb-4 space-y-5 shadow-xl flex flex-col ">
                        <div class="-ml-4 mt-8 flex justify-between items-center flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-4 flex-1">
                                <h3 class="text-lg leading-6 text-gray-900">
                                    {{ __('Antwoord') }}
                                </h3>

                                <x-input.textarea wire:model="question.answer"></x-input.textarea>
                                @error('question.answer')
                                <div class="notification error stretched mt-4">
                                    <span class="title">{{ $message }}</span>
                                </div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 2">
                    <x-content-section>
                        <x-slot name="title">{{ __('Algemeen') }}</x-slot>

                        <div class="grid grid-cols-2 gap-4">
                            <x-input.toggle-row-with-title wire:model="question.maintain_position">
                                <x-icon.locked class="flex "></x-icon.locked>
                                <span class="bold"> {{ __('Vraag vastzetten') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title wire:model="question.add_to_database">
                                <x-icon.locked class="flex "></x-icon.locked>
                                <span class="bold"> {{ __('Openbaar maken') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title wire:model="question.closable">
                                <x-icon.locked class="flex "></x-icon.locked>
                                <span class="bold"> {{ __('Sluiten na beantwoorden') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title wire:model="question.discuss">
                                <x-icon.locked class="flex "></x-icon.locked>
                                <span class="bold"> {{ __('Bespreken in de klas') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title wire:model="question.note_type">
                                <x-icon.locked class="flex "></x-icon.locked>
                                <span class="bold"> {{ __('Notities toestaan') }}</span>
                            </x-input.toggle-row-with-title>
                        </div>

                    </x-content-section>

                    <x-content-section x-data="{rtti:0, bloom:0, miller:0}">
                        <x-slot name="title">{{ __('Taxonomie') }}</x-slot>
                        <p>{{ __('Deel de vraag taxonomisch in per methode. Je kunt meerder methodes tegelijk gebruiken.') }}</p>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <x-input.toggle-row-with-title x-model="rtti">
                                    <span class="bold"> {{ __('RTTI methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="rtti" class="flex flex-col">
                                    @foreach(['R'  , 'T1' , 'T2' , 'I'] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="rtti" type="radio"
                                                   wire:model="question.rtti"
                                                   value="{{ $value }}"/>
                                            <span>{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="bloom">
                                    <span class="bold"> {{ __('BLOOM methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="bloom" class="flex flex-col">
                                    @foreach(['Onthouden', 'Begrijpen', 'Toepassen', 'Analyseren', 'Evalueren', 'Creëren'] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="bloom" type="radio"
                                                   wire:model="question.bloom"
                                                   value="{{ $value }}"/>
                                            <span>{{ __($value) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="miller">
                                    <span class="bold"> {{ __('Miller methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="miller" class="flex flex-col">
                                    @foreach(['Weten', 'Weten hoe', 'Laten zien', 'Doen',] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="miller" type="radio"
                                                   wire:model="question.miller"
                                                   value="{{ $value }}"/>
                                            <span>{{ __($value) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>


                        </div>
                    </x-content-section>

                    <x-content-section>
                        <x-slot name="title">{{ __('Eindtermen') }}</x-slot>
                        html
                    </x-content-section>

                    <x-content-section>
                        <x-slot name="title">{{ __('Leerdoelen') }}</x-slot>
                        html
                    </x-content-section>

                    <x-content-section>
                        <x-slot name="title">{{ __('Tags') }}</x-slot>
                        <x-input.tag-manager></x-input.tag-manager>
                    </x-content-section>


                </div>
            </div>


            <div class="question-editor-footer">
                <div class="question-editor-footer-button-container">
                    <button type="button" onclick="closeQuestionEditor();"
                            class="button text-button button-md">
                        <span> {{ __("Annuleer") }}</span>
                    </button>

                    <button type="button" wire:click="save" class="button cta-button button-sm">
                        <span>{{ __("Vraag opslaan") }}</span>
                    </button>
                </div>
            </div>


        </div>
