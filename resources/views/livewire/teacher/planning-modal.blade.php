<x-modal-with-footer wire:key="planningModal{{ microtime() }}" maxWidth="4xl" wire:model="showModal" show-cancel-button="false">
    <x-slot name="title">
        <div class="flex justify-between">
            <span>{{ __('teacher.Inplannen') }}</span>
            <span wire:click="$set('showModal', false)" class="cursor-pointer">x</span>
        </div>
    </x-slot>
    <x-slot name="body">
        <div class="email-section mb-4 w-full">
            <div class="mb-4">
                <label>{{ __('teacher.Naam toets of opdracht') }}</label>
                    <div class="border-blue-100 form-input w-full p-2 transition ease-in-out duration-150">{{ $test->name }}</div>

            </div>
            <div class="input-section">
                <div class="name flex mb-4 space-x-4">

                        <x-input.group class=" mb-4 sm:mb-0 flex-1" label="{{ __('teacher.Datum') }}">
                            <x-input.datepicker wire:model="request.date" locale="nl"/>
                        </x-input.group>

                    @if ($this->isAssessmentType())
                            <x-input.group class="mb-4 sm:mb-0 flex-1" label="{{ __('teacher.Datum tot') }}">
                                <x-input.select wire:model="request.date_till">
                                    @foreach(range(0, 10) as $day)
                                        <option value="{{ now()->addDay($day)->format('d-m-Y') }}">{{ now()->addDay($day)->format('d-m-Y') }}</option>
                                    @endforeach
                                </x-input.select>
                            </x-input.group>
                    @endif


                        <x-input.group class="mb-4 sm:mb-0 flex-1" label="{{ __('teacher.Periode') }}">
                            <x-input.select wire:model="request.period_id">
                                @foreach($allowedPeriods as $period)
                                    <option value="{{ $period->uuid }}">{{ $period->name }}</option>
                                @endforeach
                            </x-input.select>
                        </x-input.group>


                        <x-input.group class="mb-4 sm:mb-0 flex-1" label="{{ __('teacher.Weging') }}">
                            <x-input.text wire:model="request.weight">
                            </x-input.text>
                        </x-input.group>

                </div>
            </div>
            <div class="input-section" x-data>
                <div class="name flex">
                    <label for="teachers_and_classes">{{ __('teacher.Klassen en studenten') }}</label>
                </div>
                <div class="name flex mb-4">
                    <x-input.choices-select :multiple="true"
                                            :options="$this->schoolClasses"
                                            :withSearch="true"
                                            placeholderText="{!!  __('teacher.Klassen en studenten') !!}"
                                            wire:model="request.schoolClasses"
                                            filterContainer="selected_classes"
                                            id="teachers_and_classes"
                    />

                    <div id="selected_classes"
                         wire:key="filterkey-{{ $this->selectedClassesContainerId }}"
{{--                         wire:ignore--}}

                         class="space-x-4"
                    >
                        <template id="filter-pill-template" class="hidden">
                            <div class="space-x-2">
                                <span class="flex"></span>
                                <x-icon.close-small @click="removeFilterItem($el)"/>
                            </div>
                        </template>

                    </div>

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
                    />

                    <div id="selected_invigilators"
                         class="space-x-4"
{{--                         wire:key="{{ $this->selectedInvigilatorsContrainerId }}"--}}
                         wire:ignore
                    >
                        <template id="filter-pill-template" class="hidden">
                            <div class="space-x-2">
                                <span class="flex"></span>
                                <x-icon.close-small @click="removeFilterItem($el)"/>
                            </div>
                        </template>

                    </div>

                </div>
            </div>
            <div class="input-section">
                <div class="name flex mb-4 space-x-4">

                    <div class="input-group mb-4 sm:mb-0 flex-auto border-t ">
                        <x-input.toggle-row-with-title wire:model="request.allow_inbrowser_testing"
                                                       :toolTip="__('teacher.inbrowser_testing_tooltip')"
                                                       class="flex-row-reverse"

                        >
                            <span class="bold"> <x-icon.preview/>{{ __('teacher.Browsertoetsen toestaan') }} </span>
                        </x-input.toggle-row-with-title>
                    </div>
                    <div class="input-group mb-4 sm:mb-0 flex-auto border-t">
                        <x-input.toggle-row-with-title wire:model="request.guest_accounts"
                                                       :toolTip="__('teacher.guest_accounts_tooltip')"

                        >
                            <span class="bold">  <x-icon.preview/>{{ __('teacher.Test-Direct toestaan') }} </span>
                        </x-input.toggle-row-with-title>
                    </div>
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
            <x-button.text-button size="sm" wire:click="$set('showModal', false)">
                <span>{{__('Annuleren')}}</span>
            </x-button.text-button>
            <div>
                <x-button.primary size="sm" wire:click="planNext">
                    <span>{{__('teacher.Volgende Inplannen')}}</span>
                    <x-icon.chevron/>
                </x-button.primary>
                <x-button.cta size="sm" wire:click="plan">
                    <x-icon.checkmark/>
                    <span>{{__('teacher.Inplannen')}}</span>
                </x-button.cta>
            </div>
        </div>
    </x-slot>
</x-modal-with-footer>
