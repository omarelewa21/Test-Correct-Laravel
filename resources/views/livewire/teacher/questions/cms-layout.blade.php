<div cms id="cms" class="flex flex-1"
     x-data="constructionBody(@js($this->loading), @js($this->emptyState), @entangle('dirty'), @js($this->questionEditorId), @js($this->answerEditorId))"
     x-cloak
     x-on:question-change.window="handleQuestionChange($event.detail)"
     x-on:show-empty.window="empty = !empty"
     x-on:new-question-added.window="removeDrawingLegacy(); $nextTick(() => empty = false)"
     x-effect="if(!!empty) { $refs.editorcontainer.style.opacity = 0 }"
     questionComponent
>
    <x-partials.header.cms-editor :testName="$testName" :questionCount="$this->amountOfQuestions" />
    <div class="question-editor-content w-full relative"
         wire:key="container-{{ $this->uniqueQuestionKey }}"
         style="opacity: 0; transition: opacity 100ms ease-in-out"
         :style="{'opacity': isLoading() ? 0 : (isProcessing() ? 0 : 1)}"
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
                                <x-icon.attachment />
                                <span>{{ trans_choice('cms.bijlage', $this->attachmentsCount) }}</span>
                            </div>
                        @endif
                        <div class="inline-flex items-center gap-2.5 mx-2.5">
                            <span>
                                <x-published-tag :published="!$this->question['draft']" />
                            </span>
                            @if($this->question['closeable'])
                                <x-icon.locked />
                            @else
                                <x-icon.unlocked class="text-midgrey" />
                            @endif
                        </div>
                        <div class="relative" x-data="{questionOptionMenu: false}">
                            <button class="px-4 py-1.5 -mr-4 rounded-full hover:bg-primary hover:text-white transition-all"
                                    :class="{'bg-primary text-white' : questionOptionMenu === true}"
                                    @click="questionOptionMenu = true">
                                <x-icon.options />
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
                                    <x-icon.remove />
                                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="@if($this->needIsolate()) isolate @endif flex flex-col flex-1 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto"
             x-data="{openTab: 1}"
             x-init="$watch('openTab', value => { value === 1 ? $dispatch('tabchange') : '';})"
             @opentab.window="openTab = $event.detail; window.scrollTo({top: 0, behavior: 'smooth'})"
             selid="tabcontainer"
        >
            <div class="flex justify-end py-5" wire:ignore>
                @if($this->allowWsc)
                    <div class="flex items-center relative left-4 gap-4 mr-4" wire:ignore
                         wire:key="wsc-language-component-{{ $this->uniqueQuestionKey }}-{{$question['lang']}}">
                        <label>
                            {{ __('lang.language') }}
                        </label>
                        <x-input.select wire:model="lang"
                                        @change="changeEditorWscLanguage($event.target.dataset.value);"
                        >
                            @foreach($this->wscLanguages as $key => $language)
                                <x-input.option :value="$key" :label="$language" />
                            @endforeach
                        </x-input.select>
                    </div>
                @endif
                @if($this->showQuestionScore())
                    <x-input.score wire:model.defer="question.score"
                                   wire:key="score-component-{{ $this->uniqueQuestionKey }}"
                                   :disabled="$this->hasScoringDisabled()"
                    />
                @endif
            </div>

            <div @class(['flex flex-col gap-2 mb-4'])>
                @if($errors->isNotEmpty())
                    @foreach($errors->all() as $error)
                        <x-notification-message :title="$error" :message="null"/>
                    @endforeach
                @endif

                @if($this->isGroupQuestion() && $this->isCarouselGroup() && $this->editModeForExistingQuestion())
                    @if(isset($this->cmsPropertyBag['group_question_errors']['name']) && filled($this->cmsPropertyBag['group_question_errors']['name']))
                        @php($hasTitle = isset($this->cmsPropertyBag['group_question_errors']['title']) && filled($this->cmsPropertyBag['group_question_errors']['title']))
                        <x-notification-message>
                            @if($hasTitle)
                                <x-slot:title>
                                    <span class="title">{{ $this->cmsPropertyBag['group_question_errors']['title'] }}</span>
                                </x-slot:title>
                                <x-slot:message>
                                    <span>{{ $this->cmsPropertyBag['group_question_errors']['message'] }}</span>
                                </x-slot:message>
                            @else
                                <x-slot:title>
                                    <span>{{ $this->cmsPropertyBag['group_question_errors']['message'] }}</span>
                                </x-slot:title>
                            @endif
                        </x-notification-message>
                    @endif
                @endif


                @if($this->duplicateQuestion)
                    <x-notification-message :title="$this->isGroupQuestion() ? __('cms.duplicate_group_in_test') : __('cms.duplicate_question_in_test')" :message="null"/>
                @endif

                @if(isset($this->cmsPropertyBag['unhandled_list_changes']) && $this->cmsPropertyBag['unhandled_list_changes'])
                    <x-notification-message type="warning"
                                            title="Wijzigingen in gebruikte woordenlijst(en)"
                                            message="Er zijn wijzigingen gemaakt in de woordenlijst(en) die je hebt gebruikt voor deze vraag. Bekijk de wijzigingen van deze woordenlijst(en) en neem deze, indien gewenst, over. De wijziging bevat veranderingen aan je bestaande woorden, maar kan ook toevoegingen (verreikingen) of verwijderingen bevatten."
                    >
                        <x-slot:action>
                            <button class="text-sm bold flex gap-1 items-center hover:text-[#b5a700] transition-colors"
                                    wire:click="openViewWordListChangesModal"
                            >
                                <span>@lang('Bekijk wijzigingen woordenlijsten')</span>
                                <x-icon.arrow-small/>
                            </button>
                        </x-slot:action>
                    </x-notification-message>
                @endif
            </div>

            <x-menu.tab.container selid="tabs" max-width-class="" class="mb-[30px]">
                <x-menu.tab.item :tab="1" menu="openTab" selid="tab-question">
                    {{ __('cms.Opstellen') }}
                </x-menu.tab.item>
                <x-menu.tab.item :tab="2" menu="openTab" selid="tab-settings">
                    {{ __('cms.Instellingen') }}
                </x-menu.tab.item>
                <x-menu.tab.item :tab="3" menu="openTab" selid="tab-statistics"
                                 :when="$this->testQuestionId && $this->showStatistics()">
                    {{ __('cms.Statistiek') }}
                </x-menu.tab.item>
            </x-menu.tab.container>

            <div class="flex flex-col flex-1 pb-20 space-y-4 relative" x-show="openTab === 1"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >
                @if($this->isGroupQuestion())
                    <x-partials.group-question-basic-section />

                    @yield('upload-section-for-group-question')
                @else
                    <x-partials.question-question-section :with-upload="true"/>
                @endif

                @hasSection('question-cms-settings')
                    <x-accordion.container active-container-key="options-section"
                                           :wire:key="'option-section-'.$this->uniqueQuestionKey"
                    >
                        <x-accordion.block key="options-section"
                                           :wire:key="'option-block-'.$this->uniqueQuestionKey"
                        >
                            <x-slot:title>
                                <h4>@lang('cms.Afname instellingen')</h4>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="flex flex-col w-full">
                                    @yield('question-cms-settings')
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
                @endif

                @if($this->requiresAnswer())
                    <x-accordion.container active-container-key="answer-section"
                                           :wire:key="'answer-section-'.$this->uniqueQuestionKey"
                                           class="answer-section"
                    >
                        <x-accordion.block key="answer-section"
                                           :wire:key="'answer-block-'.$this->uniqueQuestionKey"
                        >
                            <x-slot:title>
                                <h4>{{ $this->answerSectionTitle() }}</h4>
                            </x-slot:title>
                            <x-slot:body>
                                <div class="flex flex-col w-full">
                                    @yield('question-cms-answer')
                                </div>
                            </x-slot:body>
                        </x-accordion.block>
                    </x-accordion.container>
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
                    @if($this->isSettingsGeneralPropertyVisible('autoCheckIncorrectAnswer'))
                        <x-notification-message class="items-center" type="info">
                            <x-slot:title>
                                <x-icon.autocheck />
                                <span>@lang('cms.Alle correcte antwoorden worden automatisch goed gerekend')</span>
                            </x-slot:title>
                        </x-notification-message>
                    @endif

                    <div class="general-settings-grid">
                        @if($action == 'edit' && !$isCloneRequest)
                            <div class="border-b border-bluegrey flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold text-base">{{ __('cms.unieke id') }}</span>
                                    <span class="ml-10 text-base">{{ $questionId }}</span>
                                </div>
                            </div>
                            <div class="border-b border-bluegrey flex w-full justify-between items-center py-2">
                                <div class="flex items-center space-x-2.5">
                                    <span class="bold text-base">{{ __('cms.auteur(s)') }}</span>
                                    <span class="ml-10 text-base">{{ $authors }}</span>
                                </div>
                            </div>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('closeable'))
                            <x-input.toggle-row-with-title wire:model="question.closeable"
                                                           :toolTip="__('cms.close_after_answer_tooltip_text')"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('closeable')"
                            >
                                <x-icon.locked></x-icon.locked>
                                <span class="bold">{{ $this->isGroupQuestion() ? __('cms.Deze vraaggroep afsluiten') : __('cms.Sluiten na beantwoorden') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('addToDatabase'))
                            <x-input.toggle-row-with-title wire:model="question.add_to_database"
                                                           :toolTip="__('cms.make_public_tooltip_text')"
                                                           :disabled="($question['add_to_database_disabled'] ?? false) || $this->isSettingsGeneralPropertyDisabled('addToDatabase')"
                                                           selid="open-source-switch"
                            >
                                <x-icon.preview class="flex "></x-icon.preview>
                                <span class="bold"> {{ __('cms.Openbaar maken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('maintainPosition'))
                            <x-input.toggle-row-with-title wire:model="question.maintain_position"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('maintainPosition')"
                                                           :toolTip="$this->isGroupQuestion() ? __('cms.dont_shuffle_question_group_tooltip_text') : ''"

                            >
                                <x-icon.shuffle-off />
                                <span class="bold"> {{ $this->isGroupQuestion() ? __('cms.Deze vraaggroep niet shuffelen') : __('cms.Deze vraag niet shuffelen') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('discuss'))
                            <x-input.toggle-row-with-title wire:model="question.discuss"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('discuss')"
                            >
                                <x-icon.discuss class="flex "></x-icon.discuss>
                                <span class="bold"> {{ __('cms.Bespreken in de klas') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('allowNotes'))
                            <x-input.toggle-radio-row-with-title wire:model="question.note_type"
                                                                 value-on="TEXT"
                                                                 value-off="NONE"
                                                                 :disabled="$this->isSettingsGeneralPropertyDisabled('allowNotes')"
                            >
                                <x-icon.notepad />
                                <span @class(["bold", "disabled" => $this->isSettingsGeneralPropertyVisible('allowNotes')])>
                                    {{ __('cms.Notities toestaan') }}
                                </span>
                            </x-input.toggle-radio-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('decimalScore'))
                            <x-input.toggle-row-with-title wire:model="question.decimal_score"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('decimalScore')"
                            >
                                <x-icon.half-points />
                                <span @class(["bold", "disabled" => $this->isSettingsGeneralPropertyDisabled('decimalScore')])>
                                    {{ __('cms.Halve puntenbeoordeling mogelijk') }}
                                </span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckIncorrectAnswer'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_incorrect_answer"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckIncorrectAnswer')"
                            >
                                <x-icon.autocheck/>
                                <span @class(["bold", "disabled" => $this->isSettingsGeneralPropertyDisabled('autoCheckIncorrectAnswer')])>
                                    {{ __('cms.Automatisch nakijken') }}
                                </span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isSettingsGeneralPropertyVisible('autoCheckAnswerCaseSensitive'))
                            <x-input.toggle-row-with-title wire:model="question.auto_check_answer_case_sensitive"
                                                           :disabled="$this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')"
                            >
                                <x-icon.case-sensitive />
                                <span @class(["bold", "disabled" => $this->isSettingsGeneralPropertyDisabled('autoCheckAnswerCaseSensitive')])>
                                    {{ __('cms.Hoofdlettergevoelig nakijken') }}
                                </span>
                            </x-input.toggle-row-with-title>
                        @endif

                        @if($this->isGroupQuestion())
                            <x-input.toggle-row-with-title wire:model="question.shuffle"
                                                           :tool-tip="__('cms.shuffle_questions_in_group_tooltip_text')"
                            >
                                <x-icon.shuffle />
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
                                    <x-icon.exclamation class="text-allred" />
                                    @enderror
                                    <span class="bold">RTTI {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="rtti" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->rttiOptions as $value)
                                        <x-input.radio :text-right="$value"
                                                       :value="$value"
                                                       name="rtti"
                                                       wire:key="rtti-{{ $value }}"
                                                       wire:model.defer="question.rtti"
                                        />
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="bloom">
                                    @error('question.bloom')
                                    <x-icon.exclamation class="text-allred" />
                                    @enderror
                                    <span class="bold">BLOOM {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="bloom" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->bloomOptions as $value => $translation)
                                        <x-input.radio :text-right="$translation"
                                                       :value="$value"
                                                       name="bloom"
                                                       wire:key="bloom-{{ $value }}"
                                                       wire:model.defer="question.bloom"
                                        />
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <x-input.toggle-row-with-title x-model="miller">
                                    @error('question.miller')
                                    <x-icon.exclamation class="text-allred" />
                                    @enderror
                                    <span class="bold">Miller {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="miller" class="flex flex-col gap-2.5 mt-2.5">
                                    @foreach($this->millerOptions as $value => $translation)
                                        <x-input.radio :text-right="$translation"
                                                       :value="$value"
                                                       name="miller"
                                                       wire:key="miller-{{ $value }}"
                                                       wire:model.defer="question.miller"
                                        />
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
                                                             :key="'AT-'. $this->uniqueQuestionKey" />
                                <livewire:learning-goal-manager :value="$question['learning_goals']"
                                                                :subject-id="$subjectId"
                                                                :education-level-id="$educationLevelId"
                                                                :key="'LG-'. $this->uniqueQuestionKey " />

                            </div>
                        </div>
                    </x-content-section>
                @endif

                @if($this->showSettingsTags())

                    <x-content-section>
                        <x-slot name="title">{{ __('Tags') }}</x-slot>
                        <livewire:tag-manager :init-with-tags="$this->initWithTags"
                                              :key="'TA-'. $this->uniqueQuestionKey" />
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
                                <div class="border-b border-bluegrey flex w-full justify-between items-center py-2">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="bold text-base">{{ __('cms.unieke id') }}</span>
                                            <span class="ml-10 text-base">{{ $questionId }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="border-b border-bluegrey flex w-full justify-between items-center py-2">
                                    <div class="flex items-center space-x-2.5">
                                        <div class="flex items-center space-x-2.5">
                                            <span class="bold text-base">{{ __('cms.auteur(s)') }}</span>
                                            <span class="ml-10 text-base">{{ $authors }}</span>
                                        </div>
                                    </div>
                                </div>

                                @foreach($pValues as $pValue)
                                    <x-pvalues :pValue="$pValue" />
                                @endforeach

                            @endif
                        </div>
                    </x-content-section>
                </div>
            @endif
        </div>
    </div>

    <div class="absolute">
        @livewire('side-panel')
    </div>

    <x-modal.question-editor-delete-modal />
    <x-after-planning-toast />
    <x-modal.question-editor-dirty-question-modal
            :item="strtolower($this->isGroupQuestion() ? __('cms.group-question') : __('drawing-modal.Vraag'))"
            :new="!$this->editModeForExistingQuestion()" />
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
                        x-on:click="forceSync();$wire.saveAndRefreshDrawer()"
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
            Livewire.hook("message.sent", (message, component) => {
                if (component.id === document.getElementById("cms").getAttribute("wire:id")) {
                    Alpine.store("cms").pendingRequestTally++;
                    Alpine.store("cms").handledAllRequests = false;
                }
            });
            Livewire.hook("message.processed", (message, component) => {
                if (component.id === document.getElementById("cms").getAttribute("wire:id")) {
                    Alpine.store("cms").pendingRequestTally--;
                    Alpine.store("cms").pendingRequestTimeout = setTimeout(() => {
                        if (Alpine.store("cms").pendingRequestTally === 0) {
                            Alpine.store("cms").handledAllRequests = true;
                        }
                    }, 250);
                }
                if (component.id === document.getElementById("LivewireUIModal").parentElement.getAttribute("wire:id")) {
                    if (message?.updateQueue?.method === "resetState") {
                        debugger;
                    }
                }
            });
            Livewire.on("closeModal", (force = false, skipPreviousModals = 0, destroySkipped = false) => {
                setTimeout(() => {
                    if (document.documentElement.style.overflow === "hidden") {
                        document.documentElement.style.overflow = "auto";
                    }
                }, 200);
            });
        </script>
    @endPushOnce
</div>
