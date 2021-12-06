<div>
    <div class="question-editor-header z-50">
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
        <div class=" mt-20 flex justify-end">
            <x-input.score wire:model="question.score"></x-input.score>
        </div>

        <div class="flex flex-col flex-1" x-data="{openTab:@entangle('openTab')}">
            <div class="flex w-full space-x-6 mb-5 border-b border-grey">
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


            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 1">
                <x-content-section>
                    <x-input.group label="Photo" for="photo" :error="$errors->first('upload')">
                        <x-input.filepond wire:model="upload" id="photo">
                    <span class="h-12 w-12 rounded-full overflow-hidden bg-gray-100">
                        @if ($upload)
                            <img src="{{ $upload->temporaryUrl() }}" alt="Profile Photo">
                        @else
                            <p>some picture here</p>
{{--                            <img src="{{ auth()->user()->avatarUrl() }}" alt="Profile Photo">--}}
                        @endif
                    </span>
                        </x-input.filepond>
                    </x-input.group>

                    <x-slot name="title">
                        {{ __('Vraag') }}
                    </x-slot>

                    <x-input.rich-textarea
                        wire:model.defer="question.question"
                        editorId="{{ $questionEditorId }}"
                        type="cms"
                    />
                    @error('question.question')
                    <div class="notification error stretched mt-4">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror

                </x-content-section>
                <x-content-section>
                    <x-slot name="title">
                        {{ __('Antwoord model') }}
                    </x-slot>
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
</div>
