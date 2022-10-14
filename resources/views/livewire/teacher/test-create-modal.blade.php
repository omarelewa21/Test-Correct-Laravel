<x-modal.base-modal force-close="true">
        <x-slot name="title">
                <h2>{{__("teacher.toets aanmaken")}}</h2>
        </x-slot>
        <x-slot name="content">
            <div class="flex-grow">
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

                <div class="input-section">
                    <div class="name flex mb-4 space-x-4">
                        <div class="input-group mb-4 sm:mb-0 flex-1">
                            <x-input.select
                                    wire:model="request.test_kind_id"
                            >
                                @foreach($allowedTestKinds as $type)
                                    <option value="{{ $type->id }}">{{ __('teacher.test-type-'.$type->name) }}</option>
                                @endforeach
                            </x-input.select>
                            <label for="type"
                                   class="transition ease-in-out duration-150">{{ __("Type") }}</label>
                        </div>
                        <div class="input-group  mb-4 sm:mb-0 flex-1 min-w-[250px]">
                            <x-input.select
                                    wire:model="request.subject_id"
                                    id="subject_id"

                            >
                                @foreach($allowedSubjects as $subject)
                                    <option value="{{ $subject->id }}">{!! $subject->name !!}</option>
                                @endforeach
                            </x-input.select>
                            <label for="test_kind"
                                   class="transition ease-in-out duration-150">{{ __("teacher.subject") }}</label>
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
                    </div>
                    <div class="input-section">
                        <div class="name flex mb-4 space-x-4">
                            <div class="input-group mb-4 sm:mb-0 flex-1">
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
                            <div class="input-group  mb-4 sm:mb-0 flex-1">
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
                    </div>
                    <div class="input-section">
                        <div class="name flex mb-4 space-x-4 items-center">
                            <x-input.toggle wire:model="request.shuffle"/>
                            <div class="font-bold">
                                <x-icon.shuffle/>
                                <span>{{ __('teacher.Shuffle vragen tijdens afname') }}</span></div>
                        </div>
                    </div>
                    <div class="input-section">
                        <div class="name flex mb-4 space-x-4">
                            <div class="input-group mb-4 sm:mb-0 flex-1">
                                <textarea
                                        id="name_first"
                                        wire:model.lazy="request.introduction"
                                        class="form-input @error('request.introduction') border-red @enderror"
                                >
                                </textarea>
                                <label for="name_first"
                                       class="transition ease-in-out duration-150">{{ __("teacher.introductie-tekst") }}</label>
                            </div>
                        </div>
                    </div>
                    <div class="error-section md:mb-20">
                        @error('request.name')
                        <div class="notification error mt-4">
                            <span class="title">{{ $message }}</span>
                        </div>
                        @enderror
                        @error('request.abbreviation')
                        <div class="notification error mt-4">
                            <span class="title">{{ $message }}</span>
                        </div>
                        @enderror
                        @error('request.introduction')
                        <div class="notification error mt-4">
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
                    <x-icon.arrow-left/>
                    <span>{{ __("modal.Terug") }}</span>
                </x-button.text-button>

                <div class="absolute bottom-8 left-1/2 -translate-x-1/2 h-4 flex items-center justify-center space-x-2">
                    <div class="border-0 rounded-xl bg-bluegrey h-[14px] w-[14px]"></div>
                    <div class="border-0 rounded-xl bg-primary h-[14px] w-[14px]"></div>
                </div>

                <x-button.cta wire:click="submit">
                    <span>{{ __("teacher.toets aanmaken") }}</span>
                    <x-icon.arrow/>
                </x-button.cta>
            </div>
        </x-slot>
</x-modal.base-modal>

