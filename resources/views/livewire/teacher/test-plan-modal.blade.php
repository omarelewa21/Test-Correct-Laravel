<x-modal-new>
    <x-slot name="title">
        <span>{{ __('teacher.Toets inplannen') }}</span>
    </x-slot>
    <x-slot name="body">
        <div class="email-section mb-4 w-full">
            @if($errors->isNotEmpty())
                <div class="flex flex-col gap-2.5 w-full">
                    @foreach($errors->all() as $error)
                        <div class="notification error stretched w-full">
                            <span class="title">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            <div class="mb-4">
                <label>{{ __('teacher.Naam toets of opdracht') }}</label>
                <div class="border-blue-100 form-input w-full p-2 transition ease-in-out duration-150">{{ $test->name }}</div>

            </div>
            <div class="input-section">
                <div class="name flex mb-4 flex-wrap gap-4">
                    <div class="flex flex-1 space-x-4">
                        <x-input.group class="flex flex-1" label="{{ __('teacher.Datum') }}">
                            <x-input.datepicker wire:model="request.date" locale="nl" minDate="today"/>
                        </x-input.group>


                        @if ($this->isAssessmentType())
                            <x-input.group class="flex flex-1" label="{{ __('teacher.Datum tot') }}">
                                <x-input.datepicker wire:model="request.time_end" locale="nl" minDate="today"/>
                            </x-input.group>
                        @endif
                    </div>
                    <div class="flex flex-1 space-x-4">

                        <x-input.group class="flex flex-1"  label="{{ __('teacher.Periode') }}">
                            <x-input.select class="w-full" wire:model="request.period_id">
                                @foreach($allowedPeriods as $period)
                                    <option value="{{ $period->uuid }}">{{ $period->name }}</option>
                                @endforeach
                            </x-input.select>
                        </x-input.group>

                        <x-input.group class="flex" label="{{ __('teacher.Weging') }}">
                            <input
                                    type="text"
                                    style="max-width: 100px"
                                    class=" form-input @error('request.weight') border-red @enderror"
                                    wire:model="request.weight"
                                    autocomplete="off"
                            ></x-input.group>
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
                                                    id="choices_owner"
                            />

                            <div id="selected_owner" wire:ignore class="space-x-4 ml-4"></div>
                        </div>
                    </div>
                @endif
            <div class="input-section" x-data>
                <div class="name flex">
                    <label for="teachers_and_classes">{{ __('Klassen') }}</label>
                </div>
                <div class="name flex mb-4">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->schoolClasses"
                                            :withSearch="true"
                                            placeholderText="{!!  __('teacher.Klassen') !!}"
                                            wire:model="request.school_classes"
                                            filterContainer="selected_classes"
                                            id="teachers_and_classes"
                                            hasErrors="{{ $this->getErrorBag()->has('request.school_classes') ? 'true': '' }}"
                    />
                    <div id="selected_classes" wire:ignore class="space-x-4 ml-4"></div>

                </div>
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
                                            hasErrors="{{ $this->getErrorBag()->has('request.invigilators') ? 'true': '' }}"
                    />

                    <div id="selected_invigilators" wire:ignore class="space-x-4 ml-4"></div>
                </div>
            </div>
            <div class="input-section">
                <div class="toggles | flex flex-col lg:flex-row lg:gap-x-4 flex-wrap mb-4">
                    @if($rttiExportAllowed)
                        <x-input.toggle-row-with-title wire:model="request.is_rtti_test_take"
                                                       :toolTip="__('teacher.exporteer_naar_rtti_online_tooltip')"
                                                       containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                        >
                            <x-icon.web/>
                            <span class="bold">{{ __('teacher.Exporteer naar RTTI Online') }} </span>
                        </x-input.toggle-row-with-title>
                    @endif
                    <x-input.toggle-row-with-title wire:model="request.allow_inbrowser_testing"
                                                   :toolTip="__('teacher.inbrowser_testing_tooltip')"
                                                   :disabled="$this->isAssessmentType() || !auth()->user()->schoolLocation->allow_inbrowser_testing"
                                                   containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                    >
                        <x-icon.web/>
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
        <div class="flex justify-between w-full px-2">
            <x-button.text-button size="sm" wire:click="closeModal">
                <span>{{__('Annuleren')}}</span>
            </x-button.text-button>
            <div class="flex space-x-2.5">
{{--                <x-button.primary size="sm" wire:click="planNext">--}}
{{--                    <span>{{__('teacher.Volgende Inplannen')}}</span>--}}
{{--                    <x-icon.chevron/>--}}
{{--                </x-button.primary>--}}
                <x-button.cta size="sm" wire:click="planNext">
                    <x-icon.checkmark/>
                    <span>{{__('teacher.Inplannen')}}</span>
                </x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal-new>
