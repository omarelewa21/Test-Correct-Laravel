<x-modal.base-modal>
    <x-slot:title>
        <h2>@lang('test-take.Wijzig instellingen')</h2>
    </x-slot:title>
    <x-slot:content>
        <div class="flex flex-col gap-4 text-base">
            @if($errors->isNotEmpty())
                <div class="flex flex-col gap-2.5 w-full">
                    @foreach($errors->all() as $error)
                        <div class="notification error stretched w-full">
                            <span class="title">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="name flex flex-col w-full">
                <span>@lang('teacher.Naam toets of opdracht')</span>
                <span class="w-full px-3.5 py-2 rounded-lg border border-bluegrey/50">{{ $this->testName }}</span>

            </div>

            <div class="dates-weight | flex flex-wrap gap-4 w-full">
                <div class="flex flex-1 space-x-4">
                    <x-input.group class="flex flex-1" label="{{ __('teacher.Datum') }}">
                        <x-input.datepicker wire:model="timeStart" locale="nl" minDate="today" class="bg-offwhite"/>
                    </x-input.group>


                    @if ($this->isAssignmentType())
                        <x-input.group class="flex flex-1" label="{{ __('teacher.Datum tot') }}">
                            <x-input.datepicker wire:model="timeEnd" locale="nl" minDate="today"/>
                        </x-input.group>
                    @endif
                </div>
                <div class="flex flex-1 space-x-4">
                    <x-input.group class="flex flex-1" label="{{ __('teacher.Periode') }}">
                        <x-input.select class="w-full" wire:model="testTake.period_id">
                            @foreach($allowedPeriods as $period)
                                <x-input.option :value="$period->id" :label="$period->name"/>
                            @endforeach
                        </x-input.select>
                    </x-input.group>

                    <x-input.group class="flex" label="{{ __('teacher.Weging') }}">
                        <input type="number"
                               style="max-width: 100px"
                               class=" form-input @error('testTake.weight') border-red @enderror"
                               wire:model="testTake.weight"
                               autocomplete="off"
                        ></x-input.group>
                </div>
            </div>

            <div class="students-classes | input-section flex gap-2" wire:ignore>
                <x-input.multi-dropdown-select :options="$this->schoolClasses"
                                               :title="__('teacher.Klassen en studenten')"
                                               containerId="c_and_s_edit-container-{{ $this->testTake->uuid }}"
                                               :label="__('teacher.Klassen en studenten')"
                                               wire:model.defer="classesAndStudents"
                                               :item-labels="['child_disabled' => __('test-take.Al geselecteerd')]"
                />
                <div id="c_and_s_edit-container-{{ $this->testTake->uuid }}"
                     class="flex gap-2 flex-wrap"
                ></div>
            </div>

            <div class="invigilators | input-section" x-data>
                <div class="name flex">
                    <label for="choices_invigilators">{{ __('Surveillanten') }}</label>
                </div>
                <div class="name flex">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->allowedInvigilators"
                                            :withSearch="true"
                                            placeholderText="{{ __('Docenten') }}"
                                            wire:model="selectedInvigilators"
                                            filterContainer="selected_invigilators"
                                            id="choices_invigilators"
                                            wire:key='allowed-invigilators'
                                            hasErrors="{{ $this->getErrorBag()->has('testTake.invigilators') ? 'true': '' }}"
                    />

                    <div id="selected_invigilators" wire:ignore class="space-x-4 ml-4"></div>
                </div>
            </div>

            <div class="input-section">
                <div class="toggles | flex flex-col lg:flex-row lg:gap-x-4 flex-wrap">
                    <x-input.toggle-row-with-title wire:model="testTake.allow_inbrowser_testing"
                                                   :toolTip="__('teacher.inbrowser_testing_tooltip')"
                                                   :disabled="$this->isAssignmentType() || !auth()->user()->schoolLocation->allow_inbrowser_testing"
                                                   containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                                                   selid="plan-modal-allow-browser"
                    >
                        <x-icon.web/>
                        <span class="bold">{{ __('teacher.Browsertoetsen toestaan') }} </span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title wire:model="testTake.guest_accounts"
                                                   :toolTip="__('teacher.guest_accounts_tooltip')"
                                                   :tooltipAlwaysLeft="true"
                                                   :disabled="!auth()->user()->schoolLocation->allow_guest_accounts"
                                                   containerClass="lg:border-t w-full lg:w-[calc(50%-0.5rem)]"
                                                   :error="$this->getErrorBag()->has('testTake.school_classes')"
                    >
                        <x-icon.test-direct/>
                        <span class="bold">{{ __('teacher.Test-Direct toestaan') }} </span>
                    </x-input.toggle-row-with-title>
                    @if($rttiExportAllowed)
                        <x-input.toggle-row-with-title wire:model="testTake.is_rtti_test_take"
                                                       :toolTip="__('teacher.exporteer_naar_rtti_online_tooltip')"
                                                       containerClass="w-full lg:w-[calc(50%-0.5rem)]"
                        >
                            <x-icon.export/>
                            <span class="bold">{{ __('teacher.Exporteer naar RTTI Online') }} </span>
                        </x-input.toggle-row-with-title>
                    @endif
                    @if($this->allowedToEnableMrChadd)
                        <x-input.toggle-row-with-title wire:model="testTake.enable_mr_chadd"
                                                       :toolTip="__('teacher.enable_mr_chadd_tt')"
                                                       containerClass="border-t-0 w-full lg:w-[calc(50%-0.5rem)]"
                        >
                            <x-icon.questionmark class="flex-shrink-0"/>
                            <span class="bold">{{ __('teacher.enable_mr_chadd') }} </span>
                        </x-input.toggle-row-with-title>
                    @endif

                </div>
            </div>
            <div class="input-section">
                <div class="name flex mb-4 space-x-4">
                    <x-input.group class="w-full" label="{{ __('teacher.Notities voor Surveillant') }}">
                        <x-input.textarea class="w-full" wire:model="testTake.invigilator_note">
                        </x-input.textarea>
                    </x-input.group>
                </div>
            </div>
        </div>
    </x-slot:content>

    <x-slot:footer>
        <div class="flex justify-between w-full items-center">
            <x-button.text wire:click="closeModal">
                <span>{{__('general.cancel')}}</span>
            </x-button.text>

            <x-button.cta size="md"
                          selid="plan-modal-plan-btn"
                          onClick="this.disabled = true;"
                          wire:loading.attr="disabled"
                          wire:target="save"
                          wire:click="save"
            >
                <x-icon.checkmark/>
                <span>@lang('test-take.Wijzig instellingen')</span>
            </x-button.cta>
        </div>
    </x-slot:footer>

</x-modal.base-modal>