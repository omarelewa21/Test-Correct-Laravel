<div id="cms">
    <div class="question-editor-header z-50">
        <div class="question-title">
            <div class="icon-arrow">
                <x-icon.edit></x-icon.edit>
            </div>
            <h5 class=" text-white">{{ $this->questionType }}</h5>
        </div>
        <div class="question-test-name">
            <span><?= __('cms.Toets') ?>:</span>
            <span class="bold">{{ $testName }}</span>
        </div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class=" mt-20 flex justify-end">
            <x-input.score wire:model="question.score"></x-input.score>
        </div>

        <div class="flex flex-col flex-1" x-data="{openTab:@entangle('openTab')}">
            <div class="flex w-full space-x-6 mb-5 border-b border-grey">
                <div :class="{'border-b-2 border-primary -mb-px primary' : openTab === 1}">
                    <x-button.text-button
                        style="color:inherit"
                        @click="openTab = 1"
                    >
                        {{ __('Opstellen') }}
                    </x-button.text-button>
                </div>
                <div class="" :class="{'border-b-2 border-primary -mb-px primary' : openTab === 2}">
                    <x-button.text-button
                        style="color:inherit"
                        @click="openTab = 2;"
                    >
                        {{ __('Instellingen') }}
                    </x-button.text-button>
                </div>
            </div>


            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 1">

                <x-upload.section uploadModel="uploads" :defaultFilepond="false" :multiple="true">
                    <x-slot name="files">
                        <div class="flex flex-wrap">
                            @if($attachments)
                                @foreach($attachments as $attachment)
                                    <x-attachment.badge :upload="false" :attachment="$attachment"/>
                                @endforeach
                            @endif

                            @if ($uploads)
                                @if(is_array($uploads))
                                    @foreach($uploads as $upload)
                                        <x-attachment.badge :upload="true" :attachment="$upload"/>
                                    @endforeach
                                @endif
                            @endif
                        </div>
                    </x-slot>
                    <x-slot name="filepond">
                        <x-button.secondary onclick="document.querySelector('.filepond--label-action').click()">
                            <x-icon.attachment/>
                            <span>{!! __('cms.Bijlage toevoegen')  !!}</span>
                        </x-button.secondary>
                        <span class="flex italic text-base">{!!__('cms.Of sleep je bijlage over dit vak')  !!}</span>
                    </x-slot>

                    <x-slot name="title">
                        {{ __('cms.Vraagstelling') }}
                    </x-slot>
                    @if($this->isShortOpenQuestion() || $this->isMediumOpenQuestion())
                        <x-input.rich-textarea
                            wire:model.defer="question.question"
                            editorId="{{ $questionEditorId }}"
                            type="cms"
                        />
                    @endif
                    @if($this->isCompletionQuestion())
                        <x-input.rich-textarea
                            wire:model.defer="question.question"
                            editorId="{{ $questionEditorId }}"
                            type="cms-completion"
                        />
                    @endif
                    @if($this->isSelectionQuestion())
                        <x-input.selection-textarea
                            wire:model.defer="question.question"
                            editorId="{{ $questionEditorId }}"
                        />
                    @endif
                    @error('question.question')
                    <div class="notification error stretched mt-4">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror
                </x-upload.section>

                @if($this->isShortOpenQuestion() || $this->isMediumOpenQuestion())
                    <x-content-section>
                        <x-slot name="title">
                            {{ __('cms.Antwoordmodel') }}
                        </x-slot>

                        <x-input.toggle-radio-row-with-title wire:model="question.subtype" value-on="short"
                                                             value-off="medium">
                            {{ __('cms.max_characters') }}
                        </x-input.toggle-radio-row-with-title>


                        <x-input.rich-textarea
                            wire:model.defer="question.answer"
                            editorId="{{ $answerEditorId }}"
                            type="student"
                        />

                        @error('question.answer')
                        <div class="notification error stretched mt-4">
                            <span class="title">{{ $message }}</span>
                        </div>
                        @enderror

                    </x-content-section>
                @endif
            </div>

            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 2">
                <x-content-section>
                    <x-slot name="title">{{ __('Algemeen') }}</x-slot>

                    <div class="grid grid-cols-2 gap-4">
                        @if($action == 'edit')
                            <div class="border-b flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold">{{ __('cms.unieke id') }} {{ $questionId }}</span>
                                </div>
                            </div>
                            <div class="border-b flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold">{{ __('cms.auteurs') }} {{ $testAuthors }}</span>
                                </div>
                            </div>
                        @endif

                        <x-input.toggle-row-with-title wire:model="question.maintain_position">
                            <x-icon.locked class="flex "></x-icon.locked>
                            <span class="bold"> {{ __('Vraag vastzetten') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.add_to_database">
                            <x-icon.preview class="flex "></x-icon.preview>
                            <span class="bold"> {{ __('Openbaar maken') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.closable">
                            <x-icon.close class="flex "></x-icon.close>
                            <span class="bold"> {{ __('Sluiten na beantwoorden') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.discuss">
                            <x-icon.discuss class="flex "></x-icon.discuss>
                            <span class="bold"> {{ __('Bespreken in de klas') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-radio-row-with-title wire:model="question.note_type" value-on="TEXT"
                                                             value-off="NONE">
                            <x-icon.locked class="flex "></x-icon.locked>
                            <span class="bold"> {{ __('Notities toestaan') }}</span>
                        </x-input.toggle-radio-row-with-title>
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
                    <livewire:attainment-manager/>
                </x-content-section>


                <x-content-section>
                    <x-slot name="title">{{ __('Tags') }}</x-slot>
                    <livewire:tag-manager/>
                </x-content-section>


            </div>
        </div>


        <div class="question-editor-footer">
            <div class="question-editor-footer-button-container">
                <button type="button" wire:click="returnToTestOverview();"
                        class="button text-button button-md pr-4">
                    <span> {{ __("Annuleer") }}</span>
                </button>

                <button type="button" wire:click="save" class="button cta-button button-sm">
                    <span>{{ __("Vraag opslaan") }}</span>
                </button>
            </div>
        </div>


    </div>

</div>
