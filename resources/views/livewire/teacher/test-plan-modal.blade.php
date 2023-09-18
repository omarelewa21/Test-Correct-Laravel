<x-modal-new>
    <x-slot name="title">
        <span>{{ $this->labels['title'] }}</span>
    </x-slot>
    <x-slot name="body">
        <div class="email-section mb-4 w-full text-base">
            @if($errors->isNotEmpty())
                <div class="flex flex-col gap-2.5 w-full">
                    @foreach($errors->all() as $error)
                        <div class="notification error stretched w-full">
                            <span class="title">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mb-4 flex gap-4">
                <x-input.group :label="__('teacher.Naam toets of opdracht')" class="w-full flex-1">
                    <x-input.text class="w-full !text-sysbase" :disabled="true" value="{{ $test->name }}" />
                </x-input.group>

                @yield('retake-original-date')
            </div>
            <div class="input-section">
                <div class="name flex mb-4 flex-wrap gap-4">
                    <div class="flex flex-1 space-x-4">
                        <x-input.group class="flex flex-1" :label="$this->labels['date']">
                            <x-input.datepicker wire:model="request.date" locale="nl" minDate="today" />
                        </x-input.group>


                        @if ($this->isAssignmentType())
                            <x-input.group class="flex flex-1" label="{{ __('teacher.Datum tot') }}">
                                <x-input.datepicker wire:model="request.time_end" locale="nl" minDate="today"/>
                            </x-input.group>
                        @endif
                    </div>
                    <div class="flex flex-1 space-x-4">
                        <x-input.group class="flex flex-1" label="{{ __('teacher.Periode') }}">
                            @hasSection('retake-period')
                                @yield('retake-period')
                            @else
                                <x-input.select class="w-full" wire:model="request.period_id">
                                    @foreach($allowedPeriods as $period)
                                        <x-input.option :value="$period->id" :label="$period->name" />
                                    @endforeach
                                </x-input.select>
                            @endif
                        </x-input.group>

                        <x-input.group class="flex" label="{{ __('teacher.Weging') }}">
                            @hasSection('retake-weight')
                                @yield('retake-weight')
                            @else
                                <input type="text"
                                       style="max-width: 100px"
                                       class=" form-input @error('request.weight') border-red @enderror"
                                       wire:model="request.weight"
                                       autocomplete="off"
                                />
                            @endif
                        </x-input.group>
                    </div>
                </div>
            </div>
            @if (auth()->user()->is_examcoordinator)
                <div class="input-section" x-data>
                    <div class="name flex">
                        <label for="choices_invigilators">{{ __('plan-test-take.plan_test_for') }}</label>
                    </div>
                    <div class="name flex mb-4">
                        <x-input.choices-select :multiple="false"
                                                :options="$this->allowedTeachers"
                                                :withSearch="true"
                                                placeholderText="{{ __('plan-test-take.plan_test_for') }}"
                                                wire:model="request.owner_id"
                                                filterContainer="selected_owner"
                                                wire:key='allowed-teachers'
                                                id="choices_owner"
                        />

                        <div id="selected_owner" wire:ignore class="space-x-4 ml-4"></div>
                    </div>
                </div>
            @endif
            <div @class([
                    "students-classes | input-section flex gap-2 mb-4",
                    "select-error" => $this->getErrorBag()->has('request.school_classes')
                    ])
            >
                <x-input.multi-dropdown-select :options="$this->schoolClasses"
                                               :title="__('teacher.Klassen en studenten')"
                                               containerId="c_and_s_create-container"
                                               :label="__('teacher.Klassen en studenten')"
                                               wire:model.defer="classesAndStudents"
                                               :item-labels="['child_disabled' => __('test-take.Already selected')]"
                />
                <div id="c_and_s_create-container"
                     class="flex gap-2 flex-wrap"
                     wire:ignore
                ></div>
            </div>
            <div class="input-section" x-data>
                <div class="name flex">
                    <label for="choices_invigilators">{{ __('Surveillanten') }}</label>
                </div>
                <div class="name flex mb-4">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->allowedInvigilators"
                                            :withSearch="true"
                                            placeholderText="{{ __('Docenten') }}"
                                            wire:model="request.invigilators"
                                            filterContainer="selected_invigilators"
                                            id="choices_invigilators"
                                            wire:key='allowed-invigilators'
                                            hasErrors="{{ $this->getErrorBag()->has('request.invigilators') ? 'true': '' }}"
                    />
                    <div id="selected_invigilators" wire:ignore class="space-x-4 ml-4"></div>
                </div>
            </div>
            <div class="input-section">
                <div class="toggles | flex flex-col lg:flex-row lg:gap-x-4 flex-wrap mb-4">
                    <x-input.toggle-row-with-title wire:model="request.allow_inbrowser_testing"
                                                   :toolTip="__('teacher.inbrowser_testing_tooltip')"
                                                   :disabled="$this->isAssignmentType() || !auth()->user()->schoolLocation->allow_inbrowser_testing"
                                                   containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                                                   selid="plan-modal-allow-browser"
                    >
                        <x-icon.web />
                        <span class="bold">{{ __('teacher.Browsertoetsen toestaan') }} </span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title wire:model="request.guest_accounts"
                                                   :toolTip="__('teacher.guest_accounts_tooltip')"
                                                   :tooltipAlwaysLeft="true"
                                                   :disabled="!auth()->user()->schoolLocation->allow_guest_accounts"
                                                   containerClass="lg:border-t w-full lg:w-[calc(50%-0.5rem)]"
                                                   :error="$this->getErrorBag()->has('request.school_classes')"
                    >
                        <x-icon.test-direct/>
                        <span class="bold">{{ __('teacher.Test-Direct toestaan') }} </span>
                    </x-input.toggle-row-with-title>
                    <x-input.toggle-row-with-title wire:model="request.notify_students"
                                                   :toolTip="__('teacher.notify_students_tooltip')"
                                                   containerClass="border-t-0 w-full lg:w-[calc(50%-0.5rem)]"
                    >
                        <x-icon.send-mail/>
                        <span class="bold">{{ __('teacher.notify_students') }} </span>
                    </x-input.toggle-row-with-title>
                    @if($rttiExportAllowed)
                        <x-input.toggle-row-with-title wire:model="request.is_rtti_test_take"
                                                       :toolTip="__('teacher.exporteer_naar_rtti_online_tooltip')"
                                                       containerClass="w-full lg:w-[calc(50%-0.5rem)]"
                        >
                            <x-icon.export/>
                            <span class="bold">{{ __('teacher.Exporteer naar RTTI Online') }} </span>
                        </x-input.toggle-row-with-title>
                    @endif
                    @if($this->allowedToEnableMrChadd)
                        <x-input.toggle-row-with-title wire:model="request.enable_mr_chadd"
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
                        <x-input.textarea class="w-full" wire:model="request.invigilator_note">
                        </x-input.textarea>
                    </x-input.group>
                </div>
            </div>
        </div>
    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-between w-full px-2 items-center">
            <x-button.text wire:click="closeModal">
                <span>{{__('general.cancel')}}</span>
            </x-button.text>
            <div class="flex space-x-2.5">
                <x-button.cta size="md" wire:click="planNext" selid="plan-modal-plan-btn" wire:loading.attr="disabled"
                              onClick="this.disabled = true;" wire:target="planNext" :disabled="$clickDisabled">
                    <x-icon.checkmark wire:loading.remove wire:target="planNext" />
                    <span wire:loading.remove wire:target="planNext">{{ $this->labels['cta'] }}</span>
                    <span wire:loading wire:target="planNext">{{ __('cms.one_moment_please') }}</span>
                </x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal-new>
