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

            <div class="flex flex-col flex-1" x-data="{openTab:1}">
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


                <div class="flex flex-col flex-1" x-show="openTab === 1">
                    <div class="bg-white px-4 py-5 sm:px-6">
                        <div class="-ml-4 -mt-4 flex justify-between items-center flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-4">
                                <h3 class="text-lg leading-6 text-gray-900">
                                    {{ __('Vraag') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    <div class="bg-white px-4 py-5 sm:px-6">
                        <div class="-ml-4 mt-8 flex justify-between items-center flex-wrap sm:flex-nowrap">
                            <div class="ml-4 mt-4">
                                <h3 class="text-lg leading-6 text-gray-900">
                                    {{ __('Antwoord') }}
                                </h3>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col flex-1" x-show="openTab === 2">
                        <div class="bg-white px-4 py-5 sm:px-6">{{ __('Instellingen') }}</div>

                    </div>
                </div>
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
