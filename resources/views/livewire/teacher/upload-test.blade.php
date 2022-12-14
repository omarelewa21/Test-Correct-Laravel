<div class="flex flex-col w-full upload-test min-h-screen">
    <div class="question-editor-header header w-full " style="position:sticky;">
        <div class="flex gap-4 items-center">
            <x-button.back-round class="bg-white/20 hover:text-white" wire:click="back"/>
            <h4 class="text-white">@lang('upload.Toets uploaden')</h4>
        </div>
        <div>

        </div>
    </div>
    <div class="main w-full flex-1 my-4 mx-auto max-w-3xl">
        <x-accordion.container :activeContainerKey="1">
            <x-accordion.block :key="1" :emitWhenSet="true">
                <x-slot name="title">
                    <div class="flex gap-2 items-center">
                        @if($tabOneComplete)
                            <x-icon.checkmark-circle color="var(--cta-primary)" width="30" height="30"/>
                        @else
                            <x-number-circle
                                    x-bind:class="expanded ? 'text-white bg-sysbase group-hover:bg-primary' : 'bg-transparent group-hover:text-primary group-hover:border-primary'">
                                1
                            </x-number-circle>
                        @endif
                        <h4 class="group-hover:text-primary  transition-colorstransition-colors">@lang('upload.Toetsgegevens')</h4>
                    </div>
                </x-slot>

                <x-slot name="body">
                    <div class="grid grid-cols-10 grid-flow-row w-full gap-4">
                        <x-input.group class="col-span-10 lg:col-span-7" :label="__('upload.Naam toets')">
                            <x-input.text wire:model.debounce.300ms="testInfo.name"/>
                        </x-input.group>

                        <x-input.group class="col-span-5 lg:col-span-3" :label="__('upload.Afnamedatum')">
                            <x-input.datepicker class="bg-offwhite min-w-[190px] w-full"
                                                wire:model="testInfo.planned_at"
                                                :minDate="$minimumTakeDate"
                            />
                        </x-input.group>

                        <x-input.group class="col-span-5 lg:col-span-4" :label="__('teacher.subject')">
                            <x-input.choices-select :multiple="false"
                                                    :options="$this->subjects"
                                                    :withSearch="true"
                                                    :placeholderText="__('teacher.subject')"
                                                    :searchPlaceholder="__('teacher.subject')"
                                                    wire:model="testInfo.subject_uuid"
                                                    class="super min-w-[200px]"
                            />
                        </x-input.group>
                        <x-input.group class="col-span-5 lg:col-span-3" :label="__('teacher_registered.Level')">
                            <x-input.choices-select :multiple="false"
                                                    :options="$this->educationLevels"
                                                    :withSearch="true"
                                                    :placeholderText="__('teacher_registered.Level')"
                                                    :searchPlaceholder="__('teacher_registered.Level')"
                                                    wire:model="testInfo.education_level_uuid"
                                                    class="super"
                            />
                        </x-input.group>
                        <x-input.group class="col-span-2 lg:col-span-1" :label="__('general.jaar')">
                            <x-input.choices-select :multiple="false"
                                                    :options="$this->educationLevelYears"
                                                    :withSearch="true"
                                                    :placeholderText="__('general.jaar')"
                                                    :searchPlaceholder="__('general.jaar')"
                                                    wire:model="testInfo.education_level_year"
                                                    class="super"
                                                    wire:key="years-{{ count($this->educationLevelYears) }}"
                            />
                        </x-input.group>
                        <x-input.group class="col-span-3 lg:col-span-2" :label="__('teacher.type')">
                            <x-input.choices-select :multiple="false"
                                                    :options="$this->testKinds"
                                                    :withSearch="true"
                                                    :placeholderText="__('teacher.type')"
                                                    :searchPlaceholder="__('teacher.type')"
                                                    wire:model="testInfo.test_kind_uuid"
                                                    class="super"
                            />
                        </x-input.group>

                        <div class="col-span-10 gap-4 flex flex-col">
                            <div class="flex items-center gap-4">
                                <span class="bold text-base">@lang('upload.content_check_text')</span>
                                <x-button.slider class="flex gap-2 items-center"
                                                 :options="[1 => __('general.yes'), 0 => __('general.no')]"
                                                 wire:model="testInfo.contains_publisher_content"
                                                 buttonWidth="auto"
                                />
                            </div>
                            @if($showDateWarning)
                                <div class="notification warning stretched">
                                    <div class="title">
                                        <x-icon.exclamation/>
                                        <span>@lang('upload.date_warning_title')</span>
                                    </div>
                                    <div class="body">@lang('upload.date_warning_body')</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-slot>
            </x-accordion.block>

            <x-accordion.block :key="2"
                               :disabled="!$tabOneComplete"
                               :emitWhenSet="true"
                               :upload="true"
                               uploadModel="uploads"
                               :uploadRules="$this->uploadRules"
            >
                <x-slot name="title">
                    <div class="flex gap-2 items-center">
                        @if($tabTwoComplete)
                            <x-icon.checkmark-circle color="var(--cta-primary)" width="30" height="30"/>
                        @else
                            <x-number-circle
                                    x-bind:class="expanded ? 'text-white bg-sysbase group-hover:bg-primary group-hover:border-primary' : 'bg-transparent group-hover:text-primary group-hover:border-primary'">
                                2
                            </x-number-circle>
                        @endif
                        <h4 class="group-hover:text-primary transition-colors">@lang('upload.Bestanden aanleveren')</h4>
                    </div>
                </x-slot>
                <x-slot name="body">
                    <div class="flex flex-col w-full gap-4">
                        <div class="text-lg">@lang('upload.upload_section_text')</div>
                        <div>
                            <span class="note text-xs">@lang('upload.upload_section_allowed')</span>
                            <div class="flex flex-wrap gap-x-4 gap-y-1">
                                <div class="flex gap-1.5 items-center">
                                    <x-icon.image/>
                                    <div class="flex gap-1.5 items-center">
                                        <span class="bold">@lang('upload.Afbeeldingen')</span>
                                        <span class="note text-xs">(.jpg / .jpeg / .png / .gif)</span>
                                    </div>
                                </div>

                                <div class="flex gap-1.5 items-center">
                                    <x-icon.pdf/>
                                    <div class="flex gap-1.5 items-center">
                                        <span class="bold">@lang('upload.PDF')</span>
                                    </div>
                                </div>
                                <div class="flex gap-1.5 items-center">
                                    <x-icon.word/>
                                    <div class="flex gap-1.5 items-center">
                                        <span class="bold">@lang('upload.Word')</span>
                                        <span class="note text-xs">(.doc/ .docx)</span>
                                    </div>
                                </div>
                                <div class="flex gap-1.5 items-center">
                                    <x-icon.audiofile class="primary"/>
                                    <div class="flex gap-1.5 items-center">
                                        <span class="bold">@lang('upload.Geluidsfragment')</span>
                                        <span class="note text-xs">(.mp3 / .wav)</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="inline-flex flex-wrap">
                            <div class="inline-flex flex-wrap" id="upload-dummies" wire:ignore></div>
                            @if(count($this->uploads))
                                @foreach($this->uploads as $upload)
                                    <x-attachment.badge :upload="true"
                                                        :attachment="$upload"
                                                        :title="$upload->getClientOriginalName()"
                                                        :deleteAction="sprintf('removeUpload(\'%s\')', $upload->getFilename())"
                                                        :withNumber="false"
                                    />
                                @endforeach
                            @endif
                        </div>


                        <div class="flex flex-wrap items-center gap-2">
                            <label for="file-upload">
                                <x-button.primary size="sm" type="link" class="cursor-pointer">
                                    <x-icon.attachment/>
                                    <span>@lang('cms.Bijlage toevoegen')</span>
                                </x-button.primary>
                                <input type="file" id="file-upload" multiple @change="handleFileSelect" class="hidden"/>
                            </label>
                            <span class="italic">@lang('cms.Of sleep je bijlage over dit vak')</span>
                        </div>

                        <template id="upload-badge">
                            <div class="badge inline-flex relative border rounded-lg border-blue-grey items-center mr-4 mb-2 overflow-hidden"
                                 wire:ignore>
                                <div class="flex p-2 border-r border-blue-grey h-full items-center">
                                    <x-icon.attachment/>
                                </div>
                                <div class="flex base items-center relative">
                                    <span class="badge-name p-2 note italic max-w-[236px] truncate"></span>
                                </div>
                                <div class="absolute bg-bluegrey h-1.5 w-full bottom-0">
                                    <div
                                            class="bg-primary h-1.5"
                                            style="transition: width 1s"
                                            :style="`width: ${progress[$el.closest('.badge').id]}%;`"
                                    >
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-slot>
            </x-accordion.block>

            <x-accordion.block :key="3" :disabled="!$tabOneComplete || !$tabTwoComplete" :emitWhenSet="true">
                <x-slot name="title">
                    <div class="flex gap-2 items-center">
                        <x-number-circle
                                x-bind:class="expanded ? 'text-white bg-sysbase group-hover:bg-primary group-hover:border-primary' : 'bg-transparent group-hover:text-primary group-hover:border-primary'">
                            3
                        </x-number-circle>
                        <h4 class="group-hover:text-primary transition-colors">@lang('upload.Controle')</h4>
                    </div>
                </x-slot>
                <x-slot name="body">
                    <div class="flex flex-col w-full gap-4">
                        <div class="text-lg">@lang('upload.controle_text')</div>
                        <div class="flex flex-col sm:flex-row gap-4">
                            <div class="flex flex-col flex-1 gap-1">
                                <span class="text-base bold">@lang('upload.Toetsgegevens')</span>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-0.5 grid-cols-[min-content,auto]">
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('upload.Naam toets'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $testInfo['name'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('upload.Afnamedatum'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $this->takeDateToDisplay }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('teacher.subject'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $this->selectedSubject }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('teacher.niveau'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $this->selectedLevel }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('general.jaar'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $testInfo['education_level_year'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm">
                                        <span class="min-w-max leading-6">@lang('teacher.type'):</span>
                                    </div>
                                    <div class="flex items-center col-start-2 text-lg">
                                        <span class="leading-6">{{ $this->selectedTestKind }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex flex-col gap-1">
                                <span class="bold mb-1">@lang('upload.Aangeleverde bestanden')</span>
                                <div class="flex flex-col border-bluegrey">
                                    <x-input.toggle-row-with-title container-class="border-t pt-[5px] pb-[5px]"
                                                                   wire:model="checkInfo.question_model"
                                                                   :toolTip="__('upload.question_model_tooltip')"
                                                                   :tooltipAlwaysLeft="true"
                                    >
                                        <span class="mr-6">@lang('upload.Vraagmodel')</span>
                                    </x-input.toggle-row-with-title>
                                    <x-input.toggle-row-with-title container-class="pt-[5px] pb-[5px]"
                                                                   wire:model="checkInfo.answer_model"
                                                                   :toolTip="__('upload.answer_model_tooltip')"
                                                                   :tooltipAlwaysLeft="true"
                                    >
                                        <span class="mr-6">@lang('upload.Antwoordmodel')</span>
                                    </x-input.toggle-row-with-title>
                                    <x-input.toggle-row-with-title container-class="pt-[5px] pb-[5px]"
                                                                   wire:model.defer="checkInfo.attachments"
                                                                   :toolTip="__('upload.attachments_tooltip')"
                                                                   :tooltipAlwaysLeft="true"
                                    >
                                        <span class="mr-6">@lang('cms.bijlagen')</span>
                                    </x-input.toggle-row-with-title>
                                    <x-input.toggle-row-with-title container-class="pt-[5px] pb-[5px]"
                                                                   wire:model.defer="checkInfo.elaboration_attachments"
                                                                   :toolTip="__('upload.elaboration_attachment_model_tooltip')"
                                                                   :tooltipAlwaysLeft="true"
                                    >
                                        <span class="mr-6">@lang('upload.Uitwerkbijlagen')</span>
                                    </x-input.toggle-row-with-title>
                                </div>

                            </div>
                        </div>

                        <div>
                            <div @class([
                                        'notification stretched px-2.5 py-1',
                                        'info' => $this->checkedCorrectBoxes,
                                        'error' => !$this->checkedCorrectBoxes,
                                        ])
                            >
                                <div class="title text-center">
                                    <x-icon.exclamation/>
                                    <span>@lang('upload.'.$this->checkWarningText)</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <x-button.cta class="w-full justify-center"
                                          :disabled="!$this->checkedCorrectBoxes"
                                          wire:click="finishProcess"
                                          wire:loading.attr="disabled"
                                          wire:target="finishProcess"
                                          x-on:click="$el.disabled = true"
                            >
                                <x-icon.upload/>
                                <span>@lang('upload.Toets uploaden')</span>
                            </x-button.cta>
                        </div>
                    </div>
                </x-slot>
            </x-accordion.block>
        </x-accordion.container>
    </div>

    <x-notification/>
    @livewire('livewire-ui-modal');
</div>