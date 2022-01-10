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
    <div class="max-w-7xl mx-auto mt-[70px]">
        <div class="flex w-full flex-col">
            <div class="flex w-full border-b border-secondary mt-2.5 py-2.5">
                <div class="flex w-full items-center px-4 sm:px-6 lg:px-8 justify-between">
                    <div class="flex items-center">
                        <span class="py-[5px] px-[11px] inline-flex text-center rounded-full bg-sysbase text-white text-sm">{{ $this->question['order'] ?? '1' }}</span>
                        <h2 class="ml-2.5">{{ $this->questionType }}</h2>
                    </div>
                    <div class="flex items-center">
                        <div class="inline-flex mr-2.5">
                            @if($this->question['closable'])
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
                                        @click="$dispatch('delete-modal', 'question')"
                                >
                                    <x-icon.trash/>
                                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end px-4 sm:px-6 lg:px-8 py-5">
                <x-input.score wire:model.defer="question.score"></x-input.score>
            </div>
        </div>
        <div class="flex flex-col flex-1 px-4 sm:px-6 lg:px-8" x-data="{openTab: 1}" @opentab.window="openTab = $event.detail">
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
                <div class="" :class="{'border-b-2 border-primary -mb-px primary' : openTab === 3}">
                    <x-button.text-button
                        style="color:inherit"
                        @click="openTab = 3;"
                    >
                        {{ __('cms.Statistiek') }}
                    </x-button.text-button>
                </div>
            </div>


            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 1"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >

                <x-upload.section uploadModel="uploads" :defaultFilepond="false" :multiple="true">
                    <x-slot name="files">
                        <div id="attachment-badges" class="flex flex-wrap">
                            @if($attachments)
                                @foreach($attachments as $attachment)
                                    <x-attachment.badge :upload="false" :attachment="$attachment" :title="$attachment->title"/>
                                @endforeach
                            @endif
                            @if($videos)
                                @foreach($videos as $video)
                                    <x-attachment.video-badge :video="$video"/>
                                @endforeach
                            @endif
                            @if ($uploads)
                                @if(is_array($uploads))
                                    @foreach($uploads as $upload)
                                        <x-attachment.badge :upload="true" :attachment="$upload" :title="$upload->getClientOriginalName()"/>
                                    @endforeach
                                @endif
                            @endif
                            <x-attachment.dummy-badge model="uploads"/>
                        </div>
                    </x-slot>
                    <x-slot name="filepond">
                        <x-button.add-attachment>
                            <x-slot name="text">
                                <x-icon.attachment/>
                                <span>{!! __('cms.Bijlage toevoegen')  !!}</span>
                            </x-slot>
                        </x-button.add-attachment>
                    </x-slot>

                    <x-slot name="title">
                        {{ __('cms.Vraagstelling') }}
                    </x-slot>
                    @if($this->isShortOpenQuestion() || $this->isMediumOpenQuestion())
                        <x-input.rich-textarea
                            wire:model.debounce.1000ms="question.question"
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

                @if($this->requiresAnswer())
                    <x-content-section>
                        <x-slot name="title">
                            {{ __('cms.Antwoordmodel') }}
                        </x-slot>

                        <x-input.rich-textarea
                            wire:model.debounce.1000ms="question.answer"
                            editorId="{{ $answerEditorId }}"
                            type="cms"
                        />

                        @error('question.answer')
                        <div class="notification error stretched mt-4">
                            <span class="title">{{ $message }}</span>
                        </div>
                        @enderror

                    </x-content-section>
                @endif
            </div>

            <div class="flex flex-col flex-1 pb-20 space-y-4" x-show="openTab === 2"
                 x-transition:enter="transition duration-200"
                 x-transition:enter-start="opacity-0 delay-200"
                 x-transition:enter-end="opacity-100"
            >
                <x-content-section>
                    <x-slot name="title">{{ __('Algemeen') }}</x-slot>

                    <div class="grid grid-cols-2 gap-x-4">
                        @if($action == 'edit')
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

                        <x-input.toggle-row-with-title wire:model="question.closable"
                            :toolTip="__('cms.close_after_answer_tooltip_text')"
                        >
                            <x-icon.locked></x-icon.locked>
                            <span class="bold">{{ __('Sluiten na beantwoorden') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.add_to_database"
                            :toolTip="__('cms.make_public_tooltip_text')"
                        >
                            <x-icon.preview class="flex "></x-icon.preview>
                            <span class="bold"> {{ __('Openbaar maken') }}</span>
                        </x-input.toggle-row-with-title>

                        <x-input.toggle-row-with-title wire:model="question.maintain_position">
                            <x-icon.shuffle-off/>
                            <span class="bold"> {{ __('Deze vraag niet shuffelen') }}</span>
                        </x-input.toggle-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.discuss">
                            <x-icon.discuss class="flex "></x-icon.discuss>
                            <span class="bold"> {{ __('Bespreken in de klas') }}</span>
                        </x-input.toggle-row-with-title>

                        <x-input.toggle-radio-row-with-title wire:model="question.note_type" value-on="TEXT" value-off="NONE">
                            <x-icon.notepad/>
                            <span class="bold"> {{ __('Notities toestaan') }}</span>
                        </x-input.toggle-radio-row-with-title>
                        <x-input.toggle-row-with-title wire:model="question.decimal_score">
                            <x-icon.half-points/>
                            <span class="bold"> {{ __('Halve puntenbeoordeling mogelijk') }}</span>
                        </x-input.toggle-row-with-title>
                    </div>

                </x-content-section>

                <x-content-section class="taxonomie"
                                   x-data="{ rtti:{{ $question['rtti'] ? 'true': 'false'  }}, bloom: {{ $question['bloom'] ? 'true': 'false' }}, miller: {{ $question['miller'] ? 'true': 'false' }} }">
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
                                               wire:model.defer="question.rtti"
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
                                               wire:model.defer="question.bloom"
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
                                               wire:model.defer="question.miller"
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
                    <livewire:attainment-manager :value="$question['attainments']" :subject-id="$subjectId"
                                                 :eduction-level-id="$educationLevelId"/>
                </x-content-section>


                <x-content-section>
                    <x-slot name="title">{{ __('Tags') }}</x-slot>
                    <livewire:tag-manager :init-with-tags="$initWithTags"/>
                </x-content-section>


            </div>
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
                                <div class="border-b flex w-full justify-between items-center col-span-2" wire:key="pvalue-{{ $pValue->getKey() }}" wire:ignore>
                                    <div class="flex items-center space-x-2.5 py-3">
                                        <span class="bold">{{ __('cms.p-waarde') }} {{ $pValue->education_level_year }} {{ optional($pValue->educationLevel)->name }}</span>
                                    </div>
                                    <div class="flex items-center space-x-2.5 py-3">
                                        {!! number_format( $pValue->p_value, 2) !!}
                                    </div>
                                    <div class="flex items-center space-x-2.5 py-3">
                                        {{ $pValue->p_value_count }} {{ __("cms.keer afgenomen") }}
                                    </div>

{{--                                    <div class="flex items-center space-x-2.5 py-3">--}}
{{--                                        <?--}}

{{--                                        $error = '';--}}

{{--                                        if($pvalue['p_value'] > 0.9) {--}}
{{--                                            $error = __("De vraag is te makkelijk voor dit niveau.");--}}
{{--                                        }elseif($pvalue['p_value'] < 0.2) {--}}
{{--                                            $error = __("De vraag is te moeilijk voor dit niveau. (controleer de vraag op eventuele vormfouten als u van mening bent dat de vraag geschikt is voor dit niveau)");--}}
{{--                                        }--}}

{{--                                        if(!empty($error)) {--}}
{{--                                        ?>--}}
{{--                                        <span class="fa fa-warning" onclick="Questions.showPvalueError('<?=$error?>');" style="cursor:pointer; color:orange"></span>--}}
{{--                                        <?--}}
{{--                                        }--}}
{{--                                        ?>--}}
{{--                                    </div>--}}
                                </div>
                            @endforeach

                        @endif
                    </div>
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

        <div id="delete-modal"
             class="fixed mx-auto w-full bg-gray-100 flex items-center justify-center h-screen z-[101]"
             x-data="{showModal: false, deleteAction: null}"
             x-show="showModal"
             x-cloak
             @delete-modal.window="showModal = true; deleteAction = $event.detail"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-300"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
        >

            <section class="flex flex-wrap p-4 h-full items-center">
                <!--Overlay-->
                <div class="overflow-auto"
                     style="background-color: rgba(0,0,0,0.5)"
                     :class="{ 'fixed inset-0 z-[101] flex items-center justify-center': showModal }"
                >
                    <div class="flex flex-col py-5 px-7 bg-white rounded-10 content-section"
                         @click.away="showModal = false"

                    >
                        <div class="px-2.5 flex justify-between items-center">
                            <h2>{{ __('cms.delete') }}</h2>
                            <x-icon.close class="cursor-pointer hover:text-primary" @click="showModal = false"/>
                        </div>
                        <div class="divider mb-5 mt-2.5"></div>
                        <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
                            <div class="flex flex-1 flex-col ">
                                <span x-show="deleteAction === 'question'">{{ __('cms.delete_question_confirm_text') }}</span>
                                <span x-show="deleteAction === 'attachment'">{{ __('cms.delete_attachment_confirm_text') }}</span>
                                <div class="flex w-full justify-end">

                                <x-button.cta >
                                    ja
                                </x-button.cta>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
