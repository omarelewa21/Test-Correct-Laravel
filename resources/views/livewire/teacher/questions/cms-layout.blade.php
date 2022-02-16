<div id="cms" questionComponent>
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
    <div class="max-w-7xl mx-auto mt-[70px]">
        <div class="flex w-full flex-col">

            {{--    Switch these divs to make the line stretch when the drawer is implemented
                    <div class="flex w-full border-b border-secondary mt-2.5 py-2.5">
                        <div class="flex w-full items-center px-4 sm:px-6 lg:px-8 justify-between">--}}
            <div class="flex w-full mt-2.5 px-4 sm:px-6 lg:px-8">
                <div class="flex w-full border-b border-secondary items-center justify-between py-2.5">
                    <div class="flex items-center">
                        <span class="w-8 h-8 rounded-full bg-sysbase text-white text-sm flex items-center justify-center">
                            <span>{{ $this->question['order'] == 0 ? '1' : $this->question['order']}}</span>
                        </span>
                        <h2 class="ml-2.5">{{ $this->questionType }}</h2>
                    </div>
                    <div class="flex items-center">
                        @if($this->attachmentsCount)
                            <div class="mr-2.5 flex items-center space-x-2.5">
                                <x-icon.attachment/>
                                <span>{{ trans_choice('cms.bijlage', $this->attachmentsCount) }}</span>
                            </div>
                        @endif
                        <div class="inline-flex mx-2.5">
                            @if($this->question['closeable'])
                                <x-icon.locked/>
                            @else
                                <x-icon.unlocked class="text-midgrey"/>
                            @endif
                        </div>
                        <div class="relative" x-data="{questionOptionMenu: false}">
                            <button class="px-4 py-1.5 -mr-4 rounded-full hover:bg-primary hover:text-white transition-all"
                                    :class="{'bg-primary text-white' : questionOptionMenu === true}"
                                    @click="questionOptionMenu = true">
                                <x-icon.options/>
                            </button>

                            <div x-cloak
                                 x-show="questionOptionMenu"
                                 class="absolute right-0 top-10 bg-white py-2 main-shadow rounded-10 w-72 z-30 "
                                 @click.outside="questionOptionMenu=false"
                                 x-transition:enter="transition ease-out origin-top-right duration-200"
                                 x-transition:enter-start="opacity-0 transform scale-90"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition origin-top-right ease-in duration-100"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-90"
                            >
                                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                                        @click="$dispatch('delete-modal', ['question'])"
                                >
                                    <x-icon.remove/>
                                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-4 sm:px-6 lg:px-8 ">
                @error('question.question')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
                @error('question.answer')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
                @error('question.answers')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror

                @error('question.answers.*.*')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ __('cms.De gemarkeerde velden zijn verplicht') }}</span>
                </div>
                @enderror

                @error('question.score')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ __('cms.Er dient minimaal 1 punt toegekend te worden') }}</span>
                </div>
                @enderror
                @error('question.rtti')
                <div class="notification warning stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
                @error('question.bloom')
                <div class="notification warning stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
                @error('question.miller')
                <div class="notification warning stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror

            </div>
            <div class="flex justify-end px-4 sm:px-6 lg:px-8 py-5">
                @if($this->showQuestionScore())
                    <x-input.score wire:model.defer="question.score"></x-input.score>
                @endif
            </div>

        </div>
        <div class="flex flex-col flex-1 px-4 sm:px-6 lg:px-8"
             x-data="{openTab: 1}"
             x-init="$watch('openTab', value => { value === 1 ? $dispatch('tabchange') : '';})"
             @opentab.window="openTab = $event.detail"
        >
            <div class="flex w-full space-x-6 mb-5 border-b border-secondary max-h-[50px]">
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
                @if($this->testQuestionId && $this->showStatistics())
                    <div class="" :class="{'border-b-2 border-primary -mb-px primary' : openTab === 3}">
                        <x-button.text-button
                                style="color:inherit"
                                @click="openTab = 3;"
                        >
                            {{ __('cms.Statistiek') }}
                        </x-button.text-button>
                    </div>
                @endif
            </div>


            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 1"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >
                @if($this->isPartOfGroupQuestion())
                    <x-partials.group-question-question-section/>
                @else
                    <x-partials.question-question-section/>
                @endif

                @if($this->requiresAnswer())
                    <x-content-section>
                        <x-slot name="title">
                            {{ __('cms.Antwoordmodel') }}
                        </x-slot>

                        @if($this->hasAllOrNothing())
                            <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                                           :toolTip="__('cms.all_or_nothing_tooltip_text')"
                            >
                                <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
                            </x-input.toggle-row-with-title>

                        @endif

                        @yield('question-cms-answer')
                    </x-content-section>
                @endif
            </div>

            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 2"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 x-cloak
            >
                <x-content-section>
                    <x-slot name="title">{{ __('Algemeen') }}</x-slot>

                    <div class="grid grid-cols-2 gap-x-4">
                        @if($action == 'edit' && !$isCloneRequest)
                            <div class="border-b flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold text-base">{{ __('cms.unieke id') }}</span>
                                    <span class="ml-10 text-base">{{ $questionId }}</span>
                                </div>
                            </div>
                            <div class="border-b flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold text-base">{{ __('cms.auteur(s)') }}</span>
                                    <span class="ml-10 text-base">{{ $testAuthors }}</span>
                                </div>
                            </div>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('closeable'))
                            <x-input.toggle-row-with-title wire:model="question.closeable"
                                                           :toolTip="__('cms.close_after_answer_tooltip_text')"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('closeable') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('closeable')"
                            >
                                <x-icon.locked></x-icon.locked>
                                <span class="bold">{{ __('Sluiten na beantwoorden') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('addToDatabase'))
                            <x-input.toggle-row-with-title wire:model="question.add_to_database"
                                                           :toolTip="__('cms.make_public_tooltip_text')"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('addToDatabase') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('addToDatabase')"
                            >
                                <x-icon.preview class="flex "></x-icon.preview>
                                <span class="bold"> {{ __('Openbaar maken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('maintainPosition'))
                            <x-input.toggle-row-with-title wire:model="question.maintain_position"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('maintainPosition') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('maintainPosition')"
                            >
                                <x-icon.shuffle-off/>
                                <span class="bold"> {{ __('Deze vraag niet shuffelen') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('discuss'))
                            <x-input.toggle-row-with-title wire:model="question.discuss"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('discuss') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('discuss')"
                            >
                                <x-icon.discuss class="flex "></x-icon.discuss>
                                <span class="bold"> {{ __('Bespreken in de klas') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('allowNotes'))
                            <x-input.toggle-radio-row-with-title wire:model="question.note_type" value-on="TEXT"
                                                                 value-off="NONE"
                                                                 class="{{ $this->isSettingsGeneralPropertyDisabled('allowNotes') ? 'text-disabled' : '' }}"
                                                                 :disabled="$this->isSettingsGeneralPropertyDisabled('allowNotes')"
                            >
                                <x-icon.notepad/>
                                <span class="bold"> {{ __('Notities toestaan') }}</span>
                            </x-input.toggle-radio-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('decimalScore'))
                            <x-input.toggle-row-with-title wire:model="question.decimal_score"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('decimalOption') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('decimalOption')"
                            >
                                <x-icon.half-points/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('decimalOption')) disabled @endif"> {{ __('Halve puntenbeoordeling mogelijk') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckAnswer'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_answer"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('autoCheckAnswer') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckAnswer')"
                            >
                                <x-icon.autocheck/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('autoCheckAnswer')) disabled @endif"> {{ __('Automatisch nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckAnswerCaseSensitive'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_answer_case_sensitive"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')"
                            >
                                <x-icon.case-sensitive/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')) disabled @endif"> {{ __('Hoofdletter gevoelig nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                    </div>

                </x-content-section>
                @if($this->showSettingsTaxonomy())
                    <x-content-section class="taxonomie"
                                       x-data="{
                                        rtti: $wire.entangle('rttiToggle'),
                                        bloom: $wire.entangle('bloomToggle'),
                                        miller: $wire.entangle('millerToggle')
                                        }"
                    >
                        <x-slot name="title">{{ __('Taxonomie') }}</x-slot>
                        <p class="text-base">{{ __('Deel de vraag taxonomisch in per methode. Je kunt meerder methodes tegelijk gebruiken.') }}</p>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <x-input.toggle-row-with-title x-model="rtti">
                                    @error('question.rtti')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold"> {{ __('RTTI methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="rtti" class="flex flex-col">
                                    @foreach(['R'  , 'T1' , 'T2' , 'I'] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="rtti" type="radio"
                                                   wire:model.defer="question.rtti"
                                                   value="{{ $value }}"/>
                                            <span>{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="bloom">
                                    @error('question.bloom')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold"> {{ __('BLOOM methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="bloom" class="flex flex-col">
                                    @foreach(['Onthouden', 'Begrijpen', 'Toepassen', 'Analyseren', 'Evalueren', 'Creëren'] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="bloom" type="radio"
                                                   wire:model.defer="question.bloom"
                                                   value="{{ $value }}"/>
                                            <span>{{ __($value) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="miller">
                                    @error('question.miller')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold"> {{ __('Miller methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="miller" class="flex flex-col">
                                    @foreach(['Weten', 'Weten hoe', 'Laten zien', 'Doen',] as $value)
                                        <label class="flex space-x-2.5 items-center">
                                            <input wire:key="{{ $value }}"
                                                   name="miller" type="radio"
                                                   wire:model.defer="question.miller"
                                                   value="{{ $value }}"/>
                                            <span>{{ __($value) }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>


                        </div>
                    </x-content-section>
                @endif

                @if($this->showSettingsAttainments())
                    <x-content-section>
                        <x-slot name="title">{{ __('Eindtermen') }}</x-slot>
                        <livewire:attainment-manager :value="$question['attainments']" :subject-id="$subjectId"
                                                     :eduction-level-id="$educationLevelId"/>
                    </x-content-section>
                @endif

                @if($this->showSettingsTags())

                    <x-content-section>
                        <x-slot name="title">{{ __('Tags') }}</x-slot>
                        <livewire:tag-manager :init-with-tags="$initWithTags"/>
                    </x-content-section>
                @endif

            </div>
            @if($this->showStatistics())
                <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 3"
                     x-transition:enter="transition duration-200"
                     x-transition:enter-start="opacity-0 delay-200"
                     x-transition:enter-end="opacity-100"
                >
                    <x-content-section>
                        <x-slot name="title">{{ __('cms.Statistiek') }}</x-slot>
                        <div class="grid grid-cols-2 gap-4">
                            @if($action == 'edit')
                                <div class="border-b flex w-full justify-between items-center py-2">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="bold text-base">{{ __('cms.unieke id') }}</span>
                                            <span class="ml-10 text-base">{{ $questionId }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-b flex w-full justify-between items-center py-2">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="bold text-base">{{ __('cms.auteur(s)') }}</span>
                                            <span class="ml-10 text-base">{{ $testAuthors }}</span>
                                        </div>
                                    </div>
                                </div>

                                @foreach($pValues as $pValue)
                                    <x-pvalues :pValue="$pValue"/>
                                @endforeach

                            @endif
                        </div>
                    </x-content-section>
                </div>
            @endif
        </div>

        <div class="question-editor-footer">
            <div class="question-editor-footer-button-container">

                <button
                        wire:loading.attr="disabled"
                        type="button"
                        wire:click="returnToTestOverview();"
                        class="button text-button button-md pr-4"
                >
                    <span> {{ __("Annuleer") }}</span>
                </button>


                <button
                        wire:loading.attr="disabled"
                        type="button"
                        wire:click="save"
                        class="button cta-button button-sm"
                >
                    <span>{{ __("Vraag opslaan") }}</span>
                </button>
            </div>
        </div>

        <x-modal.question-editor-delete-modal/>
        <x-notification/>
    </div>
</div>
