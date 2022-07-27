<x-modal.base-modal>
    <x-slot name="title">
        <h2>{{__("teacher.Toets instellingen")}}</h2>
    </x-slot>

    <x-slot name="content">
        <div class="flex-grow text-base">
            <div class="email-section mb-4 w-full">
                <div class="mb-4">
                    <div class="input-group w-full">
                        <input id="username" wire:model.lazy="request.name"
                               class="form-input @error('request.name') border-red @enderror"
                               autofocus>
                        <label for="username"
                               class="transition ease-in-out duration-150">{{ __("teacher.naam toets") }}</label>
                    </div>
                </div>
            </div>

            <div class="input-section ">
                <div class="name flex mb-4 flex-wrap gap-[15px]">
                    <div class="input-group mb-4 sm:mb-0 flex-1 min-w-[163px]">
                        <x-input.select
                                wire:model="request.test_kind_id"
                        >
                            @foreach($allowedTestKinds as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </x-input.select>
                        <label for="type"
                               class="transition ease-in-out duration-150">{{ __("Type") }}</label>
                    </div>
                    <div class="input-group mb-4 sm:mb-0 flex-1">
                        <x-input.select
                                wire:model="request.subject_id"
                                id="subject_id"

                        >
                            @foreach($allowedSubjects as $subject)
                                <option value="{{ $subject->id }}">{!! $subject->name !!}</option>
                            @endforeach
                        </x-input.select>
                        <label for="test_kind"
                               class="transition ease-in-out duration-150">{{ __("Vak") }}</label>
                    </div>
                    <div class="input-group mb-4 sm:mb-0 flex-1">
                        <input id="name"
                               maxlength="5"
                               wire:model.lazy="request.abbreviation"
                               class="form-input md:w-full inline-block @error('request.abbreviation') border-red @enderror"
                        >
                        <label for="name"
                               class="transition ease-in-out duration-150"
                        >
                            {{ __("teacher.Afkorting (max 5)") }}
                        </label>
                    </div>

                    <div class="input-group mb-4 sm:mb-0 flex-1" style="flex-basis:0">
                        <x-input.select
                                id="period"
                                wire:model="request.period_id"
                        >
                            @foreach($allowedPeriods as $period)
                                <option value="{{ $period->id }}">{{ $period->name }}</option>
                            @endforeach
                        </x-input.select>
                        <label for="period"
                               class="transition ease-in-out duration-150">{{ __("teacher.periode") }}</label>
                    </div>
                    <div class="input-group mb-4 sm:mb-0 flex-1">
                        <x-input.select
                                id="period"
                                wire:model="request.education_level_id"
                        >
                            @foreach($allowedEductionLevels as $educationLevel)
                                <option value="{{ $educationLevel->id }}">{{ $educationLevel->name }}</option>
                            @endforeach
                        </x-input.select>
                        <label for="name_suffix"
                               class="transition ease-in-out duration-150">{{ __("teacher.niveau") }}</label>
                    </div>
                    <div class="input-group mb-4 sm:mb-0 flex-1">
                        <x-input.select
                                id="education_level_year"
                                wire:model="request.education_level_year"
                        >
                            @foreach(range(1,$this->maxEducationLevelYear) as $levelYear)
                                <option value="{{ $levelYear }}">{{ $levelYear }}</option>
                            @endforeach
                        </x-input.select>
                        <label for="period"
                               class="transition ease-in-out duration-150">{{ __("teacher.niveau-jaar") }}</label>

                    </div>
                </div>
                <div class="input-section">
                    <div class="name flex mb-4 space-x-4 items-center">
                        <x-input.toggle wire:model="request.shuffle"/>
                        <div class="font-bold">
                            <x-icon.shuffle/>
                            <span>{{ __('teacher.Shuffle vragen tijdens afname') }}</span></div>
                    </div>
                </div>


                <div class="name flex mb-4 space-x-4">
                    <div class="input-group mb-4 sm:mb-0 flex-1"
                         x-data="{ count:0 }"
                         x-init="count = $refs.countme.value.length;"
                    >
                                <textarea
                                        id="name_first"
                                        wire:model.lazy="request.introduction"
                                        class="form-input @error('request.introduction') border-red @enderror"
                                        x-ref="countme"
                                        x-on:keyup="count = $refs.countme.value.length"
                                        maxlength="140"
                                >
                                </textarea>
                        <label for="name_first"
                               class="transition ease-in-out duration-150">{{ __("teacher.introductie-tekst") }}</label>
                        {{--                            <span class="absolute bottom-px w-full h-2 bg-lightGrey">--}}
                        {{--                                <span class="absolute rounded-10 h-2 left-0 bg-primary" :style="{'width': (count / $refs.countme.maxLength * 100) + '%', 'transition': 'width 100ms ease-in-out'}"></span>--}}
                        {{--                            </span>--}}
                    </div>
                </div>
                <div class="error-section">
                    @error('request.name')
                    <div class="notification stretched error mt-4">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror
                    @error('request.abbreviation')
                    <div class="notification stretched error mt-4">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror
                    @error('request.introduction')
                    <div class="notification stretched error mt-4">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror
                </div>
            </div>
        </div>
    </x-slot>

    <x-slot name="footer">
        <div class="flex justify-between w-full">
            <x-button.text-button wire:click="$emit('closeModal')">
                <span>{{ __("teacher.Annuleer") }}</span>
            </x-button.text-button>

            <x-button.cta wire:click="submit">
                <x-icon.checkmark/>
                <span>{{ __("general.save") }}</span>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal.base-modal>
