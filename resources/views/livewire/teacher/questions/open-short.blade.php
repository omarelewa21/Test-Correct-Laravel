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

    {{--    Switch these divs to make the line stretch when the drawer is implemented
            <div class="flex w-full border-b border-secondary mt-2.5 py-2.5">
                <div class="flex w-full items-center px-4 sm:px-6 lg:px-8 justify-between">--}}
            <div class="flex w-full mt-2.5 px-4 sm:px-6 lg:px-8">
                <div class="flex w-full border-b border-secondary items-center justify-between py-2.5">
            {{--                    --}}
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
            <div class="flex justify-end px-4 sm:px-6 lg:px-8 py-5">
                @if($this->showQuestionScore())
                <x-input.score wire:model.defer="question.score"></x-input.score>
                @endif
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
                @if($this->testQuestionId)
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
                    @if($this->isShortOpenQuestion() || $this->isMediumOpenQuestion() || $this->isMultipleChoiceQuestion() || $this->isTrueFalseQuestion() || $this->isRankingQuestion() || $this->isInfoScreenQuestion())
                        <x-input.rich-textarea
                            wire:model.debounce.1000ms="question.question"
                            editorId="{{ $questionEditorId }}"
                            type="cms"
                        />
                    @endif
                    @if($this->isCompletionQuestion())
                        <x-input.rich-textarea
                            wire:model.debounce.1000ms="question.question"
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

                        @if($this->hasAllOrNothing())
                            <x-input.toggle-row-with-title wire:model="question.all_or_nothing"
                                                           :toolTip="__('cms.all_or_nothing_tooltip_text')"
                            >
                                <span class="bold"> {{ __('cms.Alles of niets correct') }}</span>
                            </x-input.toggle-row-with-title>

                        @endif

                        @if($this->isMultipleChoiceQuestion())
                            <div class="flex w-full mt-4">{{ __('cms.MultipleChoice Question Uitleg Text') }}</div>
                            <div class="flex flex-col space-y-2 w-full mt-4"
                                 wire:sortable="forwardToService('updateMCOrder')"
                                 x-data="{}"
                                 x-init="
                                    $refs.punten.style.left = ($el.querySelector('input').offsetWidth+10) +'px';
                                   "
                                 @resize.window.debounce.100ms="$refs.punten.style.left = ($el.querySelector('input').offsetWidth+10) +'px';"
                            >
                                <div class="flex px-0 py-0 border-0 bg-system-white justify-between relative">
                                    <div class="">{{ __('cms.Antwoord') }}</div>
                                    <div wire:ignore.self x-ref="punten" class="absolute">{{ __('cms.Punten') }}</div>
                                </div>
                                @php
                                    $disabledClass = "icon disabled cursor-not-allowed";
                                    if($this->forwardToService('canDelete')) {
                                        $disabledClass = "";
                                    }
                                @endphp
                                @foreach($cmsPropertyBag['answerStruct'] as $answer)
                                    @php
                                        $answer = (object) $answer;
                                        $errorAnswerClass = '';
                                        $errorScoreClass = '';
                                    @endphp
                                    @error('question.answers.'.$loop->index.'.answer')
                                        @php
                                            $errorAnswerClass = 'border-red'
                                        @endphp
                                    @enderror
                                    @error('question.answers.'.$loop->index.'.score')
                                    @php
                                        $errorScoreClass = 'border-red'
                                    @endphp
                                    @enderror
                                    <x-drag-item id="mc-{{$answer->id}}" sortId="{{ $answer->order }}"
                                                 wireKey="option-{{ $answer->id }}" selid="drag-box"
                                                 :useHandle="true"
                                                 :keepWidth="true"
                                                 class="flex px-0 py-0 border-0 bg-system-white"
                                                 slotClasses="w-full space-x-2.5"
                                                 sortIcon="reorder"
                                                 dragIconClasses="cursor-move"
                                    >
                                        <x-input.text class="w-full  {{ $errorAnswerClass }} "
                                                      wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"
                                        />
                                        <div class=" text-center justify-center">
                                            <x-input.text class="w-12 text-center {{ $errorScoreClass }}"
                                                          wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.score"
                                                          title="{{ $answer->score }}"
                                            />
                                        </div>
                                        <x-slot name="after">
                                            <x-icon.remove class="cursor-pointer {{ $disabledClass }}"
                                                           id="remove_{{ $answer->order }}"
                                                           wire:click="forwardToService('delete', '{{$answer->id}}')"/>
                                        </x-slot>
                                    </x-drag-item>
                                @endforeach
                            </div>
                            <div class="flex flex-col space-y-2 w-full">
                                <x-button.primary class="mt-3 justify-center" wire:click="forwardToService('addAnswerItem')">
                                    <x-icon.plus/>
                                    <span >
                                    {{ __('cms.Item toevoegen') }}
                                    </span>
                                </x-button.primary>
                            </div>
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

                        @elseif($this->isTrueFalseQuestion())
                            <div class="flex  col-2 space-x-2 w-full mt-4">
                                <div class="flex inline-block w-full items-center">{{ __('cms.Is bovenstaande vraag/stelling juist of onjuist?') }}</div>
                                <div class="inline-flex bg-off-white max-w-max border border-blue-grey rounded-lg truefalse-container transition duration-150">
                                    @foreach( ['true', 'false'] as $optionValue)

                                        <label id="truefalse-{{$optionValue}}" wire:key="truefalse-{{$optionValue}}"
                                               for="link{{ $optionValue }}"
                                               class="bg-off-white border border-off-white rounded-lg trueFalse bold transition duration-150
                                          @if($loop->iteration == 1) true border-r-0 @else false border-l-0 @endif
                                               {!! $cmsPropertyBag['tfTrue'] === $optionValue ? 'active' : '' !!}"
                                        >
                                            <input
                                                    wire:model="cmsPropertyBag.tfTrue"
                                                   id="link{{ $optionValue }}"
                                                   name="Question_TrueFalse"
                                                   type="radio"
                                                   class="hidden"
                                                   value="{{ $optionValue }}"
                                                   selid="testtake-radiobutton"
                                            >
                                            <span>
                                                @if ($optionValue == 'true')
                                                    <x-icon.checkmark/>
                                                @else
                                                    <x-icon.close/>
                                                @endif
                                            </span>
                                        </label>
                                        @if($loop->first)
                                            <div class="bg-blue-grey" style="width: 1px; height: 30px; margin-top: 3px"></div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @elseif($this->isRankingQuestion())
                            <div class="flex w-full mt-4">
                                {{ __('cms.Ranking Question Uitleg Text') }}
                            </div>
                            <div class="flex flex-col space-y-2 w-full mt-4"
                                 wire:sortable="forwardToService('updateRankingOrder')">
                                <div class="flex px-0 py-0 border-0 bg-system-white">
                                    <div class="w-full mr-2">{{ __('cms.Stel je te rankgschikken items op') }}</div>
                                    <div class="w-20"></div>
                                </div>
                                @php
                                    $disabledClass = "icon disabled cursor-not-allowed";
                                    if($this->forwardToService('canDelete')) {
                                        $disabledClass = "";
                                    }
                                @endphp
                                @foreach($cmsPropertyBag['answerStruct'] as $answer)
                                    @php
                                        $answer = (object) $answer;
                                        $errorAnswerClass = '';
                                    @endphp
                                    @error('question.answers.'.$loop->index.'.answer')
                                    @php
                                        $errorAnswerClass = 'border-red'
                                    @endphp
                                    @enderror
                                    <x-drag-item id="mc-{{$answer->id}}" sortId="{{ $answer->order }}"
                                                 wireKey="option-{{ $answer->id }}" selid="drag-box"
                                                 class="flex px-0 py-0 border-0 bg-system-white relative"
                                                 slotClasses="w-full mr-0 "
                                                 dragClasses="absolute right-14 hover:text-primary transition"
                                                 dragIconClasses=" cursor-move"
                                                 :useHandle="true"
                                                 :keepWidth="true"
                                                 sortIcon="reorder"
                                    >
                                        <x-input.text class="w-full mr-1 {{ $errorAnswerClass }} " wire:model.lazy="cmsPropertyBag.answerStruct.{{ $loop->index }}.answer"/>
                                        <x-slot name="after">
                                            <x-icon.remove class="mx-2 w-4 cursor-pointer  {{ $disabledClass }}" id="remove_{{ $answer->order }}" wire:click="forwardToService('delete','{{$answer->id}}')"></x-icon.remove>
                                        </x-slot>
                                    </x-drag-item>
                                @endforeach
                            </div>
                            <div class="flex flex-col space-y-2 w-full">
                                <x-button.primary class="mt-3 justify-center" wire:click="forwardToService('addAnswerItem')">
                                    <x-icon.plus/>
                                    <span >
                                    {{ __('cms.Item toevoegen') }}
                                    </span>
                                </x-button.primary>
                            </div>
                            @error('question.answers.*.*')
                            <div class="notification error stretched mt-4">
                                <span class="title">{{ __('cms.De gemarkeerde velden zijn verplicht') }}</span>
                            </div>
                            @enderror
                        @else
                            <x-input.rich-textarea
                                wire:model.debounce.1000ms="question.answer"
                                editorId="{{ $answerEditorId }}"
                                type="cms"
                            />
                        @endif

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

                        <x-input.toggle-row-with-title wire:model="question.closeable"
                            :toolTip="__('cms.close_after_answer_tooltip_text')"
                           class="{{ $this->isCloseableDisabled() ? 'text-disabled' : '' }}"
                           :disabled="$this->isCloseableDisabled()"
                        >
                            <x-icon.locked></x-icon.locked>
                            <span class="bold">{{ __('Sluiten na beantwoorden') }}</span>
                        </x-input.toggle-row-with-title>

                        <x-input.toggle-row-with-title wire:model="question.add_to_database"
                            :toolTip="__('cms.make_public_tooltip_text')"
                           class="{{ $this->isAddToDatabaseDisabled() ? 'text-disabled' : '' }}"
                           :disabled="$this->isAddToDatabaseDisabled()"
                        >
                            <x-icon.preview class="flex "></x-icon.preview>
                            <span class="bold"> {{ __('Openbaar maken') }}</span>
                        </x-input.toggle-row-with-title>


                        <x-input.toggle-row-with-title wire:model="question.maintain_position"
                           class="{{ $this->isMaintainPositionDisabled() ? 'text-disabled' : '' }}"
                           :disabled="$this->isMaintainPositionDisabled()"
                        >
                            <x-icon.shuffle-off/>
                            <span class="bold"> {{ __('Deze vraag niet shuffelen') }}</span>
                        </x-input.toggle-row-with-title>

                        <x-input.toggle-row-with-title wire:model="question.discuss"
                               class="{{ $this->isDiscussDisabled() ? 'text-disabled' : '' }}"
                               :disabled="$this->isDiscussDisabled()"
                        >
                            <x-icon.discuss class="flex "></x-icon.discuss>
                            <span class="bold"> {{ __('Bespreken in de klas') }}</span>
                        </x-input.toggle-row-with-title>


                        <x-input.toggle-radio-row-with-title wire:model="question.note_type" value-on="TEXT" value-off="NONE"
                           class="{{ $this->isAllowNotesDisabled() ? 'text-disabled' : '' }}"
                           :disabled="$this->isAllowNotesDisabled()"
                        >
                            <x-icon.notepad/>
                            <span class="bold"> {{ __('Notities toestaan') }}</span>
                        </x-input.toggle-radio-row-with-title>

                        <x-input.toggle-row-with-title wire:model="question.decimal_score"
                            class="{{ $this->isDecimalOptionDisabled() ? 'text-disabled' : '' }}"
                            :disabled="$this->isDecimalOptionDisabled()"
                        >
                            <x-icon.half-points/>
                            <span class="bold @if($this->isDecimalOptionDisabled()) disabled @endif"> {{ __('Halve puntenbeoordeling mogelijk') }}</span>
                        </x-input.toggle-row-with-title>

                    </div>

                </x-content-section>

                <x-content-section class="taxonomie"
                                   x-data="{
                                       rtti:{{ $question['rtti'] ? 'true': 'false'  }},
                                       bloom: {{ $question['bloom'] ? 'true': 'false' }},
                                       miller: {{ $question['miller'] ? 'true': 'false' }}
                                   }"
                >
                    <x-slot name="title">{{ __('Taxonomie') }}</x-slot>
                    <p class="text-base">{{ __('Deel de vraag taxonomisch in per methode. Je kunt meerder methodes tegelijk gebruiken.') }}</p>
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

        <x-modal.question-editor-delete-modal />
    </div>
</div>
