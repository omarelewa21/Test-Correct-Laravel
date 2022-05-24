<x-modal wire:key="planningModal" maxWidth="4xl" wire:model="showModal" show-cancel-button="false">
    <x-slot name="title">{{ __('teacher.Inplannen') }}</x-slot>
    <x-slot name="body">
        <x-input.group label="{{ __('teacher.Naam toets of opdracht') }}">
            <div class="border-blue-100 border-2 w-full p-2"
                 class="transition ease-in-out duration-150">{{ $test->name }}</div>
        </x-input.group>
        <x-input.group label="{{ __('teacher.Datum') }}">
            <x-input.text wire:model="request.date"/>
            <x-input.datepicker wire:model="request.date" locale="nl"/>

        </x-input.group>
@if ($this->isAssessmentType())
        <x-input.group label="{{ __('teacher.Datum tot') }}">
            <x-input.select wire:model="request.date_till">
                @foreach(range(0, 10) as $day)
                    <option value="{{ now()->addDay($day)->format('d-m-Y') }}">{{ now()->addDay($day)->format('d-m-Y') }}</option>
                @endforeach
            </x-input.select>
        </x-input.group>
        @endif

        <x-input.group label="{{ __('teacher.Periode') }}">
            <x-input.select wire:model="request.period_id">
                @foreach($allowedPeriods as $period)
                    <option value="{{ $period->uuid }}">{{ $period->name }}</option>
                @endforeach
            </x-input.select>
        </x-input.group>

        <x-input.group label="{{ __('teacher.Weging') }}">
            <x-input.text wire:model="request.weight">
            </x-input.text>
        </x-input.group>

        <x-input.group label="{{ __('teacher.Klassen en studenten') }}">

        </x-input.group>

        <x-input.group label="{{ __('teacher.Surveillanten') }}">

        </x-input.group>
        <label class="font-bold"><x-icon.preview/>{{ __('teacher.Test-Direct toestaan') }}</label>
        <x-input.toggle wire:model="request.guest_accounts"/>

        <label class="font-bold"><x-icon.preview/>{{ __('teacher.Browsertoetsen toestaan') }}</label>

        <x-input.toggle wire:model="request.allow_inbrowser_testing"/>


        <x-input.group label="{{ __('teacher.Notities voor Surveillant') }}">
            <x-input.textarea wire:model="request.invigilator_note">
            </x-input.textarea>
        </x-input.group>
    </x-slot>
    <x-slot name="actionButton">
        <x-button.primary size="sm" wire:click="planNext">
            <span>{{__('teacher.Volgende Inplannen')}}</span>
            <x-icon.chevron/>
        </x-button.primary>
        <x-button.primary size="sm" wire:click="plan">
            <span>{{__('teacher.Inplannen')}}</span>
            <x-icon.chevron/>
        </x-button.primary>
    </x-slot>
</x-modal>
