<div class="flex flex-col w-full upload-test min-h-screen">
    <div class="question-editor-header header w-full " style="position:sticky;">
        <div class="flex gap-4 items-center">
            <x-button.back-round class="bg-white/20 hover:text-white" wire:click="back"/>
            <h4 class="text-white">Toets uploaden {{ $this->formUuid }}</h4>
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
                            <x-input.text wire:model="testInfo.name"/>
                        </x-input.group>

                        <x-input.group class="col-span-5 lg:col-span-3" :label="__('upload.Afnamedatum')">
                            <x-input.datepicker class="bg-offwhite min-w-[200px]"
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
                                                    :options="[['value'=> 1, 'label' => '1'], ['value'=> 2, 'label' => '2'], ['value'=> 2, 'label' => '3'], ['value'=> 2, 'label' => '4']]"
                                                    :withSearch="true"
                                                    :placeholderText="__('general.jaar')"
                                                    :searchPlaceholder="__('general.jaar')"
                                                    wire:model="testInfo.education_level_year"
                                                    class="super"
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
                                                 :options="[__('general.yes'), __('general.no')]"
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

            <x-accordion.block :key="2" :disabled="!$tabOneComplete" :emitWhenSet="true" :upload="true"
                               uploadModel="uploads">
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
                                    <x-icon.pdf-file/>
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


                        <div class="flex items-center gap-2">
                            <label for="file-upload">
                                <x-button.primary size="sm" type="link" class="cursor-pointer">
                                    <x-icon.attachment/>
                                    <span>@lang('cms.Bijlage toevoegen')</span>
                                </x-button.primary>
                                <input type="file" id="file-upload" multiple @change="handleFileSelect" class="hidden"
                                       x-on:livewire-upload-start="console.log('start')"
                                />
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

            <x-accordion.block :key="3" :disabled="false/*!$tabOneComplete || !$tabTwoComplete*/" :emitWhenSet="true">
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
                        <div class="flex">
                            <div>
                                <span class="text-base">@lang('upload.Toetsgegevens')</span>
                                <div class="grid grid-cols-2 gap-x-6 gap-y-0.5">
                                    <div class="flex items-center col-start-1 note text-sm"><span>Naam toets:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span>{{ $testInfo['name'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm"><span>Afnamedatum:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span>{{ $testInfo['planned_at'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm"><span>Vak:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span></span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm"><span>Niveau:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span>{{ $testInfo['name'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm"><span>Jaar:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span>{{ $testInfo['name'] }}</span>
                                    </div>
                                    <div class="flex items-center col-start-1 note text-sm"><span>Type:</span></div>
                                    <div class="flex items-center col-start-2 text-lg"><span>{{ $testInfo['name'] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-slot>
            </x-accordion.block>
        </x-accordion.container>
    </div>
</div>