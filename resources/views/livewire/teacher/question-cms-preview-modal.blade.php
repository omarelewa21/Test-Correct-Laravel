<div cms id="cms-preview" class="flex flex-1 flex-col bg-lightGrey h-full overflow-auto"
>
    <div class="question-editor-preview-header flex w-full bg-white items-center px-6 py-4 fixed z-10">
        <div class="bold flex items-center min-w-max space-x-2.5 text-lg">
            <x-icon.preview/>
            <span>{{ __('teacher.Vraag voorbeeld') }}:</span>
        </div>

        <h3 class="line-clamp-1 break-all px-2.5">{{ $this->questionTitle }}</h3>

        <div class="flex ml-auto items-center space-x-2.5">
            @if($this->inTest)
                <span title="{{ __('cms.Deze vraag is aanwezig in de toets.') }}">
                    <x-icon.checkmark-circle color="var(--cta-primary)"/>
                </span>
            @endif
            <x-button.cta x-data="{}" x-cloak x-show="Alpine.store('questionBank').active" size="sm" wire:click="addQuestion">
                <x-icon.plus-2/>
                <span>{{ __('cms.Toevoegen') }}</span>
            </x-button.cta>

            <x-button.close wire:click="$emit('closeModal')"/>
        </div>
    </div>
    <div class="question-editor-content w-full max-w-7xl mx-auto relative" wire:ignore.self>
        <div class="flex w-full flex-col">
            <div class="flex w-full border-b border-secondary mt-2.5 py-2.5">
                <div class="flex w-full items-center px-4 sm:px-6 lg:px-8 justify-between">
                    <div class="flex items-center">
                        <h2 selid="question-type-title">{{ $this->questionType }}</h2>
                    </div>
                    <div class="flex items-center">
                        @if($this->attachmentsCount)
                            <div class="mr-2.5 flex items-center space-x-2.5 ">
                                <x-icon.attachment/>
                                <span>{{ trans_choice('cms.bijlage', $this->attachmentsCount) }}</span>
                            </div>
                        @endif
                        <div class="inline-flex mx-2.5 text-midgrey">
                            @if($this->question['closeable'])
                                <x-icon.locked/>
                            @else
                                <x-icon.unlocked/>
                            @endif
                        </div>
                        <div class="flex text-midgrey ml-2.5">
                            <x-icon.options/>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex justify-end px-4 sm:px-6 lg:px-8 py-5">
                @if($this->showQuestionScore())
                    <x-input.score wire:model.defer="question.score"
                                   wire:key="score-component-{{ $this->uniqueQuestionKey }}"
                                   :disabled="true"
                    />
                @endif
            </div>

        </div>
        <div class="flex flex-col flex-1 px-4 sm:px-6 lg:px-8"
             x-data="{openTab: 1}"
             {{--Dispatch tabchange when everything is rendered so drag item width is fixed--}}
             x-init="setTimeout(() => { $dispatch('tabchange') }, 300)"
             selid="tabcontainer"
        >
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
                @if($this->showStatistics())
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


            <div class="flex flex-col flex-1 pb-5 space-y-4 relative" x-show="openTab === 1"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >
                @if($this->isGroupQuestion())
                    <x-partials.group-question-basic-section/>

                    @yield('upload-section-for-group-question')
                @elseif($this->isPartOfGroupQuestion())
                    <x-partials.group-question-question-section/>
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


            <div class="flex flex-col flex-1 pb-5 space-y-4" x-show="openTab === 2"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
                 x-cloak
            >
                <x-content-section>
                    <x-slot name="title">{{ __('cms.Algemeen') }}</x-slot>

                    <div class="general-settings-grid">
                        <div class="border-b flex w-full justify-between items-center py-2">
                            <div class="flex items-center space-x-2.5">
                                <span class="bold text-base">{{ __('cms.unieke id') }}</span>
                                <span class="ml-10 text-base">{{ $questionId }}</span>
                            </div>
                        </div>
                        <div class="border-b flex w-full justify-between items-center py-2">
                            <div class="flex items-center space-x-2.5">
                                <span class="bold text-base">{{ __('cms.auteur(s)') }}</span>
                                <span class="ml-10 text-base"></span>
                            </div>
                        </div>

                        <x-input.toggle-row-with-title :disabled="true"
                                                       :toolTip="__('cms.close_after_answer_tooltip_text')"
                                                       :checked="$this->questionModel->closeable"
                        >
                            <x-icon.locked/>
                            <span>{{ __('cms.Sluiten na beantwoorden') }}</span>
                        </x-input.toggle-row-with-title>

                        <x-input.toggle-row-with-title :disabled="true"
                                                       :toolTip="__('cms.make_public_tooltip_text')"
                                                       :checked="$this->questionModel->add_to_database"
                        >
                            <x-icon.preview/>
                            <span>{{ __('cms.Openbaar maken') }}</span>
                        </x-input.toggle-row-with-title>

                        @if(!$this->questionModel->isType('Group'))
                            <x-input.toggle-row-with-title :disabled="true"
                                                           :checked="$this->questionModel->allow_notes"
                            >
                                <x-icon.notepad/>
                                <span>{{ __('cms.Notities toestaan') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title :disabled="true"
                                                           :checked="$this->questionModel->decimal_score"
                            >
                                <x-icon.half-points/>
                                <span>{{ __('cms.Halve puntenbeoordeling mogelijk') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif
                        @if($this->questionModel->isType('Completion'))
                            <x-input.toggle-row-with-title :disabled="true"
                                                           :checked="$this->questionModel->auto_check_answer"
                            >
                                <x-icon.autocheck/>
                                <span>{{ __('cms.Automatisch nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                            <x-input.toggle-row-with-title :disabled="true"
                                                           :checked="$this->questionModel->auto_check_answer_case_sensitive"
                            >
                                <x-icon.case-sensitive/>
                                <span>{{ __('cms.Hoofdletter gevoelig nakijken') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif
                        @if($this->questionModel->isType('Group'))
                            <x-input.toggle-row-with-title :disabled="true"
                                                           :checked="$this->questionModel->shuffle"
                            >
                                <x-icon.shuffle/>
                                <span>{{ __('cms.Vragen in deze group shuffelen') }}</span>
                            </x-input.toggle-row-with-title>
                        @endif
                    </div>

                </x-content-section>
                @if($this->showSettingsTaxonomy())
                    <x-content-section>
                        <x-slot name="title">{{ __('cms.Taxonomie') }}</x-slot>
                        <p class="text-base">{{ __('cms.Deel de vraag taxonomisch in per methode. Je kunt meerder methodes tegelijk gebruiken.') }}</p>
                        <div class="flex w-full gap-4"
                             x-data="{rtti: @js($this->questionModel->rtti), bloom: @js($this->questionModel->bloom), miller: @js($this->questionModel->miller) }"
                        >
                            <div class="flex flex-1 flex-col">
                                <x-input.toggle-row-with-title x-model="rtti"
                                                               disabled="true"
                                                               :checked="filled($this->questionModel->rtti)"
                                >
                                    <span class="bold">RTTI {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="rtti" class="flex flex-col pt-2 gap-2.5">
                                    @foreach(['R'  , 'T1' , 'T2' , 'I'] as $value)
                                        <label class="radio-custom">
                                            <input wire:key="rtti-{{ $value }}"
                                                   name="rtti"
                                                   type="radio"
                                                   value="{{ $value }}"
                                                   disabled
                                                   @if($value === $this->questionModel->rtti) checked @endif
                                            />
                                            <span class="ml-2">{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col">
                                <x-input.toggle-row-with-title x-model="bloom"
                                                               :disabled="true"
                                                               :checked="filled($this->questionModel->bloom)"
                                >
                                    <span class="bold">BLOOM {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="bloom" class="flex flex-col pt-2 gap-2.5">
                                    @foreach([ __('cms.Onthouden'), __('cms.Begrijpen'), __('cms.Toepassen'), __('cms.Analyseren'), __('cms.Evalueren'), __('cms.Creëren')] as $value)
                                        <label class="radio-custom">
                                            <input wire:key="bloom-{{ $value }}"
                                                   name="bloom"
                                                   type="radio"
                                                   value="{{ $value }}"
                                                   @if($value === $this->questionModel->bloom) checked @endif
                                            />
                                            <span class="ml-2">{{ $value }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="flex flex-1 flex-col">
                                <x-input.toggle-row-with-title x-model="miller"
                                                               :disabled="true"
                                                               :checked="filled($this->questionModel->miller)"
                                >
                                    <span class="bold">Miller {{ __('cms.methode') }}</span>
                                </x-input.toggle-row-with-title>
                                <div x-show="miller" class="flex flex-col pt-2 gap-2.5">
                                    @foreach([ __('cms.Weten'), __('cms.Weten hoe'), __('cms.Laten zien'), __('cms.Doen'),] as $value)
                                        <label class="radio-custom">
                                            <input wire:key="miller-{{ $value }}"
                                                   name="miller"
                                                   type="radio"
                                                   value="{{ $value }}"
                                                   disabled
                                                   @if($value === $this->questionModel->miller) checked @endif
                                            />
                                            <span class="ml-2">{{ __($value) }}</span>
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
                            <div class="grid grid-cols-2 gap-x-6 mt-4 relative">
                                <livewire:attainment-manager :value="$question['attainments']"
                                                             :subject-id="$subjectId"
                                                             :eduction-level-id="$educationLevelId"
                                                             :key="'AT-'. $this->uniqueQuestionKey"
                                                             :disabled="true"
                                />
                                <livewire:learning-goal-manager :value="$question['learning_goals']"
                                                                :subject-id="$subjectId"
                                                                :eduction-level-id="$educationLevelId"
                                                                :key="'LG-'. $this->uniqueQuestionKey "
                                                                :disabled="true"
                                />
                            </div>
                        </div>
                    </x-content-section>
                @endif

                @if($this->showSettingsTags())
                    <x-content-section>
                        <x-slot name="title">{{ __('Tags') }}</x-slot>
                        <div class="flex gap-2.5">
                            @forelse($this->initWithTags as $tag)
                                <span class="bg-system-secondary px-5 py-2 rounded-10 bold">{{ $tag->name }}</span>
                            @empty
                                <span>{{ __('cms.Deze vraag heeft geen tags') }}</span>
                            @endforelse
                        </div>
                    </x-content-section>
                @endif

            </div>
            @if($this->showStatistics())
                <div class="flex flex-col flex-1 pb-5 space-y-4" x-show="openTab === 3"
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
</div>
