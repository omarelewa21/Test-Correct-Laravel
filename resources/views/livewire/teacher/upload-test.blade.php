<div class="flex flex-col w-full upload-test min-h-screen">
    <div class="question-editor-header header w-full " style="position:sticky;">
        <div class="flex gap-4 items-center">
            <x-button.back-round class="bg-white/20 hover:text-white" wire:click="back"/>
            <h4 class="text-white">Toets uploaden {{ $this->formUuid }}</h4>
        </div>
        <div>
            <x-button.cta>
                <x-icon.upload/>
                <span>Uploaden</span>
            </x-button.cta>
        </div>
    </div>
    <div class="main w-full flex-1 my-4">
        <x-accordion.container :activeOnInit="1">
            <x-accordion.block :key="1">
                <x-slot name="title">
                    Toetsgegevens
                </x-slot>

                <x-slot name="titleLeft">
                    <span class="ml-auto mr-2.5 gap-x-1.5 inline-flex mid-grey font-size-14 bold items-center uppercase">
                        <x-icon.exclamation/>
                        <span class="mt-0.5">{{ __('Verplicht') }}</span>
                    </span>
                </x-slot>

                <x-slot name="body">
                    <div class="grid grid-cols-10 grid-flow-row w-full gap-4">
                        <x-input.group class="col-span-10 lg:col-span-7" :label="__('Naamtoets')">
                            <x-input.text wire:model="name"/>
                        </x-input.group>

                        <x-input.group class="col-span-5 lg:col-span-3" :label="__('Afnamedatum')">
                            <x-input.datepicker class="bg-offwhite min-w-[200px]" wire:model="planned_at"/>
                        </x-input.group>

                        <x-input.group class="col-span-5 lg:col-span-4" :label="__('teacher.subject')">
                            <x-input.choices-select :multiple="false"
                                                    :options="[['value'=> 1, 'label' => 'engels'], ['value'=> 2, 'label' => 'frans']]"
                                                    :withSearch="true"
                                                    placeholderText="Selecteer een vak..."
                                                    wire:model="typedetails.subject_id"
                                                    class="super min-w-[200px]"
                                                    searchPlaceholder="Selecteer een vak..."
                            />
                        </x-input.group>
                        <x-input.group class="col-span-5 lg:col-span-3" :label="__('teacher_registered.Level')">
                            <x-input.choices-select :multiple="false"
                                                    :options="[['value'=> 1, 'label' => 'mavo'], ['value'=> 2, 'label' => 'mavo / vmbo tl']]"
                                                    :withSearch="true"
                                                    placeholderText=""
                                                    wire:model="typedetails.education_level_id"
                            />
                        </x-input.group>
                        <x-input.group class="col-span-2 lg:col-span-1" :label="__('general.Leerjaar')">
                            <x-input.choices-select :multiple="false"
                                                    :options="[['value'=> 1, 'label' => '1'], ['value'=> 2, 'label' => '2'], ['value'=> 2, 'label' => '3'], ['value'=> 2, 'label' => '4']]"
                                                    :withSearch="true"
                                                    placeholderText=""
                                                    wire:model="typedetails.education_level_year"
                            />
                        </x-input.group>
                        <x-input.group class="col-span-3 lg:col-span-2" :label="__('teacher.type')">
                            <x-input.choices-select :multiple="false"
                                                    :options="[['value'=> 1, 'label' => 'Formatief'], ['value'=> 2, 'label' => 'Summatief']]"
                                                    :withSearch="true"
                                                    placeholderText="avc"
                                                    wire:model="typedetails.test_kind_id"
                            />
                        </x-input.group>

                        <div class="col-span-10 gap-4 flex flex-col">
                            <div class="flex items-center gap-4">
                                <span class="bold text-base">Bevat deze tpets cpntent van een uitgeverij?</span>
                                <x-button.slider class="flex gap-2 items-center"
                                                 :options="[__('general.yes'), __('general.no')]"
                                                 wire:model="contains_publisher_content"
                                                 buttonWidth="auto"
                                />
                            </div>
                            <div class="notification warning stretched">
                                <div class="title">Kaas</div>
                                <div class="body">is lekker </div>
                            </div>

                        </div>
                    </div>
                </x-slot>
            </x-accordion.block>


            <x-accordion.block :key="2">
                <x-slot name="title">
                    Antwoorden
                </x-slot>
                <x-slot name="titleLeft">
                    <span class="ml-auto mr-2.5 gap-x-1.5 inline-flex mid-grey font-size-14 bold items-center uppercase">
                        <x-icon.exclamation/>
                        <span class="mt-0.5">{{ __('Verplicht') }}</span>
                    </span>
                </x-slot>
                <x-slot name="body">
                    body!
                </x-slot>
            </x-accordion.block>
            <x-accordion.block :key="3">
                <x-slot name="title">
                    ssss
                </x-slot>
                <x-slot name="body">
                    body!
                </x-slot>
            </x-accordion.block>
            <x-accordion.block :key="4">
                <x-slot name="title">
                    ssss
                </x-slot>
                <x-slot name="body">
                    body!
                </x-slot>
            </x-accordion.block>
        </x-accordion.container>
    </div>
</div>