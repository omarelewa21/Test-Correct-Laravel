<x-modal-new>
    <x-slot name="title">
        <span>{{ __('teacher.Toets inplannen') }}</span>
    </x-slot>
    <x-slot name="body">
        <div class="email-section mb-4 w-full">
            @if($errors->count())
                <div class="notification stretched error mt-4">
                    @error('request.school_classes')
                    <div class="title">{{ $message }}</div>
                    @enderror
                    @error('request.weight')
                    <div class="title">{{ $message }}</div>
                    @enderror
                    @error('request.date')
                    <div class="title">{{ $message }}</div>
                    @enderror
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
                            <x-input.datepicker wire:model="request.date" locale="nl" min-date="today"/>
                        </x-input.group>


                        @if ($this->isAssessmentType())
                            <x-input.group class="flex flex-1" label="{{ __('teacher.Datum tot') }}">
                                <x-input.datepicker wire:model="request.time_end" locale="nl" min-date="today"/>
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
                                            hasErrors="{{ $errors->has('request.schoolClasses') ? 'true': '' }}"
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
                    />

                    <div id="selected_invigilators" wire:ignore class="space-x-4 ml-4"></div>
                </div>
            </div>
            <div class="input-section">
                <div class="name flex mb-4 space-x-4">

                    @if(! $this->isAssessmentType())
                    <div class="input-group mb-4 sm:mb-0 flex-auto border-t ">
                        <x-input.toggle-row-with-title wire:model="request.allow_inbrowser_testing"
                                                       :toolTip="__('teacher.inbrowser_testing_tooltip')"


                        >
                            <x-icon.web/>
                            <span class="bold">{{ __('teacher.Browsertoetsen toestaan') }} </span>
                        </x-input.toggle-row-with-title>
                    </div>

                    @endif

                    <div class="input-group mb-4 sm:mb-0 flex-auto border-t @error('request.school_classes') border-red-500 @enderror">
                        @if(auth()->user()->schoollocation->allow_guest_accounts)
                            <x-input.toggle-row-with-title wire:model="request.guest_accounts"
                                                           :toolTip="__('teacher.guest_accounts_tooltip')"
                                                           :tooltipAlwaysLeft="true"

                            >
                                <x-icon.test-direct/>
                                <span class="bold">{{ __('teacher.Test-Direct toestaan') }} </span>
                            </x-input.toggle-row-with-title>
                        @endif
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
