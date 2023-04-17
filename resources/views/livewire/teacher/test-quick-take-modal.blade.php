<x-modal-new>
    <x-slot name="title">
        {{__("cms.Direct afnemen")}}
    </x-slot>

    <x-slot name="body">
        <div class="flex flex-col gap-4">
            <div class="data | flex flex-col gap-x-4 gap-y-2.5">
                <div class="flex w-full gap-4">
                    <x-input.group class="flex-1" :label="__('teacher.Naam toets of opdracht')">
                        <x-input.text class="w-full" wire:model="testName" title="{{ $testName }}" :disabled="true" />
                    </x-input.group>
                    {{--                </div>--}}
                    {{--                <div class="flex gap-4">--}}
                    <x-input.group :label="__('teacher.Weging')">
                        <x-input.text class="w-[100px]"
                                      title="{{ __('teacher.Weging') }}"
                                      wire:model="testTake.weight"
                        />
                    </x-input.group>
                </div>
                <div class="flex gap-4">
                    <x-input.group :label="__('header.Klassen')">
                        <x-input.choices-select :multiple="true"
                                                :options="$this->schoolClasses"
                                                :withSearch="true"
                                                placeholderText="{!!  __('header.Klassen') !!}"
                                                wire:model="selectedClasses"
                                                filterContainer="selected_classes"
                                                id="classes"
                                                hasErrors="{{ $errors->has('selectedClasses') ? 'true': '' }}"
                                                                                                class="short-list"
                                                                        />
                                                                    </x-input.group>
                                                                    <div id="selected_classes" wire:ignore class="flex flex-wrap gap-2 self-end relative -top-0.5 mt-6" style=""></div>
                                                                </div>
                                                            </div>
                                                            <div class="toggles | flex flex-col lg:flex-row lg:gap-x-4 flex-wrap">
                                                                <x-input.toggle-row-with-title wire:model="testTake.allow_inbrowser_testing"
                                                                                               :toolTip="__('teacher.inbrowser_testing_tooltip')"
                                                                                               :disabled="$this->isAssignmentType()"
                                                                                               containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                                                                >
                                                                    <x-icon.web/>
                                                                    <span class="bold">{{ __('teacher.Browsertoetsen toestaan') }} </span>
                </x-input.toggle-row-with-title>
                <x-input.toggle-row-with-title wire:model="testTake.guest_accounts"
                                               :toolTip="__('teacher.guest_accounts_tooltip')"
                                               :tooltipAlwaysLeft="true"
                                               containerClass="lg:border-t w-full lg:w-[calc(50%-0.5rem)]"
                >
                    <x-icon.test-direct />
                    <span class="bold">{{ __('teacher.Test-Direct toestaan') }} </span>
                </x-input.toggle-row-with-title>

                <x-input.toggle-row-with-title wire:model="testTake.notify_students"
                                               :toolTip="__('teacher.notify_students_tooltip')"
                                               containerClass="border-t-0 w-full lg:w-[calc(50%-0.5rem)]"
                >
                    <x-icon.send-mail />
                    <span class="bold">{{ __('teacher.notify_students') }} </span>
                </x-input.toggle-row-with-title>
                @if ($this->showSpellCheckerToggle())
                        <x-input.toggle-row-with-title wire:model="request.allow_wsc"
                                                    containerClass="border-t-0 w-full lg:w-[calc(50%-0.5rem)]"
                        >
                            <x-icon.autocheck />
                            <span class="bold">{{ __('teacher.allow_wsc') }} </span>
                        </x-input.toggle-row-with-title>
                    @endif
                @if($rttiExportAllowed)
                    <x-input.toggle-row-with-title wire:model="testTake.is_rtti_test_take"
                                                   :toolTip="__('teacher.exporteer_naar_rtti_online_tooltip')"
                                                   containerClass="border-t w-full lg:w-[calc(50%-0.5rem)]"
                    >
                        <x-icon.export />
                        <span class="bold">{{ __('teacher.Exporteer naar RTTI Online') }} </span>
                    </x-input.toggle-row-with-title>
                @endif
            </div>

            @if($errors->isNotEmpty())
                <div class="flex flex-col gap-2.5 w-full">
                    @foreach($errors->all() as $error)
                        <div class="notification error stretched w-full">
                            <span class="title">{{ $error }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </x-slot>

    <x-slot name="footer">
        <div class="flex mt-4 gap-4 w-full">
            <x-button.text-button wire:click="$emit('closeModal')" size="sm" class="ml-auto">
                <span>{{ __("teacher.Annuleer") }}</span>
            </x-button.text-button>

            <x-button.cta wire:click="plan" size="sm" wire:loading.attr="disabled" wire:target="plan">
                <x-icon.checkmark  wire:loading.remove wire:target="plan"/>
                <span wire:loading.remove wire:target="plan">{{ __("regular-staff.Toets afnemen") }}</span>
                <span wire:loading wire:target="plan">{{ __('cms.one_moment_please') }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal-new>