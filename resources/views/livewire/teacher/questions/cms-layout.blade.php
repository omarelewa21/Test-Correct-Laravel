<div cms id="cms" class="flex flex-1"
     x-data="{
            loading: @js($loading),
            empty: @js($this->emptyState),
            dirty: @entangle('dirty'),
            loadTimeout: null
             }"
     x-init="
           handleQuestionChange = (evt) => {
                $store.cms.loading = true;
                loading = true;
                $wire.set('loading', true);
                if(typeof evt !== 'undefined') empty = false;
                removeDrawingLegacy();
                window.scrollTo({top: 0, behavior: 'smooth'});
                $store.cms.dirty = false;
           }

           loadingTimeout = (value) => {
                if (value === true) {
                    loadTimeout = setTimeout(() => {
                        $store.cms.loading = false;
                        $store.cms.processing = false;
                        $wire.set('loading', false);
                        clearTimeout(loadTimeout);
                    }, 1000)
                }
           }

           $watch('$store.cms.loading', (value) => loadingTimeout(value));
           $watch('loading', (value) => loadingTimeout(value));
           $watch('dirty', (value) => $store.cms.dirty = value);

           removeDrawingLegacy = () => {
                $root.querySelector('#drawing-question-tool-container')?.remove();
           }
           changeEditorWscLanguage = (lang) => {
                if (document.getElementById('{{ $this->questionEditorId }}')) {
                    WebspellcheckerTlc.lang(ClassicEditors['{{ $this->questionEditorId }}'], lang);
                }
                if (document.getElementById('{{ $this->answerEditorId }}')) {
                    WebspellcheckerTlc.lang(ClassicEditors['{{ $this->answerEditorId }}'], lang);
                }
           }
           forceSyncEditors = () => {
                if (document.getElementById('{{ $this->questionEditorId }}')) {
                    $wire.sync('question.question', ClassicEditors['{{ $this->questionEditorId }}'].getData());
                }
                if (document.getElementById('{{ $this->answerEditorId }}')) {
                    $wire.sync('question.answer', ClassicEditors['{{ $this->answerEditorId }}'].getData());
                }
           }
           "
     x-cloak
     x-on:question-change.window="handleQuestionChange($event.detail)"
     x-on:show-empty.window="empty = !empty"
     x-on:new-question-added.window="removeDrawingLegacy(); $nextTick(() => empty = false)"
     x-on:store-current-question.window="forceSyncEditors();"
     x-effect="if(!!empty) { $refs.editorcontainer.style.opacity = 0 }"
     questionComponent
>
    <x-partials.header.cms-editor :testName="$testName" :questionCount="$this->amountOfQuestions"/>
    <div class="question-editor-content w-full relative"
         wire:key="container-{{ $this->uniqueQuestionKey }}"
         style="opacity: 0; transition: opacity .3s ease-in"
         :style="{'opacity': ($store.cms.loading || $store.cms.emptyState) ? 0 : ($store.cms.processing) ? 0 : 1}"
         x-ref="editorcontainer"
         wire:ignore.self
    >

        <div class="flex w-full flex-col sticky top-[var(--header-height)] bg-lightGrey z-10">
            <div class="flex w-full border-b border-secondary mt-2.5 py-2.5">
                <div class="flex w-full items-center px-4 sm:px-6 lg:px-8 justify-between">
                    <div class="flex items-center">
                        @if(!$this->isGroupQuestion())
                            <span class="w-8 h-8 rounded-full bg-sysbase text-white text-sm flex items-center justify-center">
                            <span>{{ $this->resolveOrderNumber() }}</span>
                        </span>
                        @endif
                        <h2 class="ml-2.5" selid="question-type-title">{{ $this->questionType }}</h2>
                    </div>
                    <div class="flex items-center">
                        @if($this->attachmentsCount)
                            <div class="mr-2.5 flex items-center space-x-2.5">
                                <x-icon.attachment/>
                                <span>{{ trans_choice('cms.bijlage', $this->attachmentsCount) }}</span>
                            </div>
                        @endif
                        <div class="inline-flex items-center gap-2.5 mx-2.5">
                            <span>
                                <x-published-tag :published="!$this->question['draft']"/>
                            </span>
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
                                        wire:click="removeItem('question', 1)"
                                >
                                    <x-icon.remove/>
                                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col flex-1 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
             x-data="{openTab: 1}"
             x-init="$watch('openTab', value => { value === 1 ? $dispatch('tabchange') : '';})"
             @opentab.window="openTab = $event.detail; window.scrollTo({top: 0, behavior: 'smooth'})"
             selid="tabcontainer"
        >
            <div class="flex justify-end py-5" wire:ignore>
                @if(\Illuminate\Support\Facades\Auth::user()->schoolLocation->allow_wsc)
                    <div class="flex items-center relative left-4 gap-4 mr-4" wire:ignore
                         wire:key="wsc-language-component-{{ $this->uniqueQuestionKey }}-{{$question['lang']}}">
                        <label>
                            {{ __('lang.language') }}
                        </label>
                        <x-input.select
                                wire:model="lang"
                                @change="changeEditorWscLanguage($event.target.value);"
                        >
                            <option value="nl_NL">{{ __('lang.nl_NL') }}</option>
                            <option value="en_GB">{{ __('lang.en_GB') }}</option>
                            <option value="fr_FR">{{ __('lang.fr_FR') }}</option>
                            <option value="de_DE">{{ __('lang.de_DE') }}</option>
                            <option value="es_ES">{{ __('lang.es_ES') }}</option>
                            <option value="it_IT">{{ __('lang.it_IT') }}</option>
                        </x-input.select>
                    </div>
                @endif
                @if($this->showQuestionScore())
                    <x-input.score wire:model.defer="question.score"
                                   wire:key="score-component-{{ $this->uniqueQuestionKey }}"
                    />
                @endif
            </div>
            <div class="{{ $this->getErrorBag()->isEmpty() ? 'mb-4' : '' }}">
                @error('question.name')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ $message }}</span>
                </div>
                @enderror
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
                @error('question.answer_svg')
                <div class="notification error stretched mt-4">
                    <span class="title">{{ __('cms.drawing-question-required-answer') }}</span>
                </div>
                @enderror

                @if($this->isGroupQuestion() && $this->isCarouselGroup() && $this->editModeForExistingQuestion())
                    @if(!$this->hasEnoughSubQuestionsAsCarousel())
                        <div class="notification warning stretched mt-4">
                            <span class="title">{{ __('cms.carousel_not_enough_questions') }}</span>
                        </div>
                    @endif
                    @if(!$this->hasEqualScoresForSubQuestions())
                        <div class="notification warning stretched mt-4">
                            <span class="title">{{ __('cms.carousel_subquestions_scores_differ') }}</span>
                        </div>
                    @endif
                @endif

                @if($this->duplicateQuestion)
                    <div class="notification error stretched mt-4">
                        <span class="title">{{ $this->isGroupQuestion() ? __('cms.duplicate_group_in_test') : __('cms.duplicate_question_in_test') }}</span>
                    </div>
                @endif
            </div>
            <div class="flex w-full space-x-6 mb-5 border-b border-secondary max-h-[50px]" selid="tabs">
                <div :class="{'border-b-2 border-primary -mb-px primary' : openTab === 1}" selid="tab-question">
                    <x-button.text-button
                            style="color:inherit"
                            @click="openTab = 1"
                    >
                        {{ __('cms.Opstellen') }}
                    </x-button.text-button>
                </div>
                <div class="" :class="{'border-b-2 border-primary -mb-px primary' : openTab === 2}"
                     selid="tab-settings">
                    <x-button.text-button
                            style="color:inherit"
                            @click="openTab = 2;"
                    >
                        {{ __('cms.Instellingen') }}
                    </x-button.text-button>
                </div>
                @if($this->testQuestionId && $this->showStatistics())
                    <div class="" :class="{'border-b-2 border-primary -mb-px primary' : openTab === 3}"
                         selid="tab-statistics">
                        <x-button.text-button
                                style="color:inherit"
                                @click="openTab = 3;"
                        >
                            {{ __('cms.Statistiek') }}
                        </x-button.text-button>
                    </div>
                @endif
            </div>


            <div class="flex flex-col flex-1 pb-20 space-y-4 relative" x-show="openTab === 1"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >
                @if($this->isGroupQuestion())
                    <x-partials.group-question-basic-section/>

                    @yield('upload-section-for-group-question')
                @else
                    <x-partials.question-question-section/>
                @endif

                @if($this->requiresAnswer())
                    <x-content-section>
                        <x-slot name="title">
                            {{ __('cms.Antwoordmodel') }}
                        </x-slot>

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
                    <x-slot name="title">{{ __('cms.Algemeen') }}</x-slot>

                    <div class="general-settings-grid">
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
                                    <span class="ml-10 text-base">{{ $authors }}</span>
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
                                <span class="bold">{{ $this->isGroupQuestion() ? __('cms.Deze vraaggroep afsluiten') : __('cms.Sluiten na beantwoorden') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('addToDatabase'))
                            <x-input.toggle-row-with-title wire:model="question.add_to_database"
                                                           :toolTip="__('cms.make_public_tooltip_text')"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('addToDatabase') ? 'text-disabled' : '' }}"
                                                           :disabled="($question['add_to_database_disabled'] ?? false) || $this->isSettingsGeneralPropertyDisabled('addToDatabase')"
                                                           selid="open-source-switch"
                            >
                                <x-icon.preview class="flex "></x-icon.preview>
                                <span class="bold"> {{ __('cms.Openbaar maken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('maintainPosition'))
                            <x-input.toggle-row-with-title wire:model="question.maintain_position"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('maintainPosition') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('maintainPosition')"
                                                           :toolTip="$this->isGroupQuestion() ? __('cms.dont_shuffle_question_group_tooltip_text') : ''"

                            >
                                <x-icon.shuffle-off/>
                                <span class="bold"> {{ $this->isGroupQuestion() ? __('cms.Deze vraaggroep niet shuffelen') : __('cms.Deze vraag niet shuffelen') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('discuss'))
                            <x-input.toggle-row-with-title wire:model="question.discuss"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('discuss') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('discuss')"
                            >
                                <x-icon.discuss class="flex "></x-icon.discuss>
                                <span class="bold"> {{ __('cms.Bespreken in de klas') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('allowNotes'))
                            <x-input.toggle-radio-row-with-title wire:model="question.note_type" value-on="TEXT"
                                                                 value-off="NONE"
                                                                 class="{{ $this->isSettingsGeneralPropertyDisabled('allowNotes') ? 'text-disabled' : '' }}"
                                                                 :disabled="$this->isSettingsGeneralPropertyDisabled('allowNotes')"
                            >
                                <x-icon.notepad/>
                                <span class="bold"> {{ __('cms.Notities toestaan') }}</span>
                            </x-input.toggle-radio-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('decimalScore'))
                            <x-input.toggle-row-with-title wire:model="question.decimal_score"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('decimalOption') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('decimalOption')"
                            >
                                <x-icon.half-points/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('decimalOption')) disabled @endif"> {{ __('cms.Halve puntenbeoordeling mogelijk') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('spellingCheckAvailableDuringAssessing'))
                            <x-input.toggle-row-with-title wire:model="question.spell_check_available"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('spellingCheckAvailableDuringAssessing') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('spellingCheckAvailableDuringAssessing')"
                            >
                                <x-icon.autocheck/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('spellingCheckAvailableDuringAssessing')) disabled @endif"> {{ __('cms.Taalcontrole beschikbaar tijdens nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckAnswer'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_answer"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('autoCheckAnswer') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckAnswer')"
                            >
                                <x-icon.autocheck/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('autoCheckAnswer')) disabled @endif"> {{ __('cms.Automatisch nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckAnswerCaseSensitive'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_answer_case_sensitive"
                                                           class="{{ $this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive') ? 'text-disabled' : '' }}"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')"
                            >
                                <x-icon.case-sensitive/>
                                <span class="bold @if($this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')) disabled @endif"> {{ __('cms.Hoofdletter gevoelig nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isGroupQuestion())
                            <x-input.toggle-row-with-title wire:model="question.shuffle"
                                                           :tool-tip="__('cms.shuffle_questions_in_group_tooltip_text')"
                            >
                                <x-icon.shuffle/>
                                <span class="bold">{{ __('cms.Vragen in deze group shuffelen')}}</span>
                            </x-input.toggle-row-with-title>
                        @endif
                    </div>

                </x-content-section>
                @if($this->showSettingsTaxonomy())
                    <x-content-section class=""
                                       x-data="{
                                        rtti: $wire.entangle('rttiToggle'),
                                        bloom: $wire.entangle('bloomToggle'),
                                        miller: $wire.entangle('millerToggle')
                                        }"
                    >
                        <x-slot name="title">{{ __('cms.Taxonomie') }}</x-slot>
                        <p class="text-base">{{ __('cms.Deel de vraag taxonomisch in per methode. Je kunt meerder methodes tegelijk gebruiken.') }}</p>
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <x-input.toggle-row-with-title x-model="rtti">
                                    @error('question.rtti')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold">RTTI {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="rtti" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->rttiOptions as $value)
                                        <label class="radio-custom">
                                            <input wire:key="{{ $value }}"
                                                   name="rtti"
                                                   type="radio"
                                                   wire:model.defer="question.rtti"
                                                   value="{{ $value }}"/>
                                            <span class="ml-2.5">{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="bloom">
                                    @error('question.bloom')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold">BLOOM {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="bloom" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->bloomOptions as $value => $translation)
                                        <label class="radio-custom">
                                            <input wire:key="{{ $value }}"
                                                   name="bloom"
                                                   type="radio"
                                                   wire:model.defer="question.bloom"
                                                   value="{{ $value }}"/>
                                            <span class="ml-2.5">{{ $translation  }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="miller">
                                    @error('question.miller')
                                    <x-icon.exclamation class="text-allred"/>
                                    @enderror
                                    <span class="bold">Miller {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="miller" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->millerOptions as $value => $translation)
                                        <label class="radio-custom">
                                            <input wire:key="{{ $value }}"
                                                   name="miller"
                                                   type="radio"
                                                   wire:model.defer="question.miller"
                                                   value="{{ $value }}"/>
                                            <span class="ml-2.5">{{ $translation }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>


                        </div>
                    </x-content-section>
                @endif

                @if($this->showSettingsAttainments())
                    <x-content-section>
                        <x-slot name="title">{{ __('cms.Eindtermen & Leerdoelen') }}</x-slot>
                        <div class="flex flex-col flex-2">
                            <p class="text-base">{{ __('cms.Selecteer het domein en het subdomein waaraan deze vraag bijdraagt.') }}</p>
                            <div class="grid grid-cols-2 gap-x-6 mt-4">
                                <livewire:attainment-manager :value="$question['attainments']"
                                                             :subject-id="$subjectId"
                                                             :education-level-id="$educationLevelId"
                                                             :key="'AT-'. $this->uniqueQuestionKey"/>
                                <livewire:learning-goal-manager :value="$question['learning_goals']"
                                                                :subject-id="$subjectId"
                                                                :education-level-id="$educationLevelId"
                                                                :key="'LG-'. $this->uniqueQuestionKey "/>

                            </div>
                        </div>
                    </x-content-section>
                @endif

                @if($this->showSettingsTags())

                    <x-content-section>
                        <x-slot name="title">{{ __('Tags') }}</x-slot>
                        <livewire:tag-manager :init-with-tags="$this->initWithTags"
                                              :key="'TA-'. $this->uniqueQuestionKey"/>
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
                                            <span class="ml-10 text-base">{{ $authors }}</span>
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
    </div>
    <x-modal.question-editor-delete-modal/>
    <x-modal.question-editor-dirty-question-modal
            :item="strtolower($this->isGroupQuestion() ? __('cms.group-question') : __('drawing-modal.Vraag'))"
            :new="!$this->editModeForExistingQuestion()"/>
    @if(!$this->withDrawer)
        <div class="question-editor-footer" x-data>
            <div class="question-editor-footer-button-container">

                <button
                        type="button"
                        class="button text-button button-md pr-4"
                        wire:loading.attr="disabled"
                        wire:click="returnToTestOverview();"
                        selid="cancel-btn"
                >
                    <span> {{ __("auth.cancel") }}</span>
                </button>


                <button
                        type="button"
                        class="button cta-button button-sm save_button"
                        wire:loading.attr="disabled"
                        {{--                    wire:click="saveAndRefreshDrawer()"--}}
                        @click="forceSyncEditors();$wire.saveAndRefreshDrawer()"
                        x-data="{disabled: false}"
                        x-init="$watch('$store.questionBank.active', value => disabled = value);"
                        x-on:beforeunload.window="disabled = true"
                        x-on:filepond-start.window="disabled = true"
                        x-on:filepond-finished.window="disabled = false"
                        :disabled="!!empty || disabled"
                        selid="save-btn"
                >
                    <span>{{ __("drawing-modal.Opslaan") }}</span>
                </button>
            </div>
        </div>
    @endif
    @pushOnce('scripts')
    <script>
        Livewire.hook('message.sent', (message, component) => {
            if (component.id === document.getElementById('cms').getAttribute('wire:id')) {
                Alpine.store('cms').pendingRequestTally++;
                Alpine.store('cms').handledAllRequests = false;
            }
        });
        Livewire.hook('message.processed', (message, component) => {
            if (component.id === document.getElementById('cms').getAttribute('wire:id')) {
                Alpine.store('cms').pendingRequestTally--;
                Alpine.store('cms').pendingRequestTimeout = setTimeout(() => {
                    if (Alpine.store('cms').pendingRequestTally === 0) {
                        Alpine.store('cms').handledAllRequests = true;
                    }
                }, 250)
            }
        });
    </script>
    @endPushOnce
</div>
