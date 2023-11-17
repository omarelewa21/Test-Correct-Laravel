<div class="flex-1 py-4 mx-8 " id="school-locations-grid" x-data="">
    <div class="flex flex-1 justify-between">
        <div><h1>{{ __('school_location.school_locations') }}</h1></div>
        <div class="flex-shrink-0">
            @if($administrator)
                <x-button.cta class="" wire:click="addNewSchoolLocation">
                    <span>{{ __('school_location.new-school-location') }}</span>
                </x-button.cta>
            @endif
        </div>

    </div>
    <div class="flex flex-col py-4">
        <div class="flex w-full mt-2">
            <div class="relative w-full">
                <x-input.group class="w-full">
                    <x-input.text class="w-full"
                                  placeholder="{{ __('cms.Search...') }}"
                                  wire:model="filters.combined_search"
                    />
                    <x-icon.search class="absolute right-0 -top-2"/>
                </x-input.group>
            </div>
        </div>
        <div class="flex flex-wrap w-full gap-2 mt-2">

            <x-input.choices-select
                    wire:key="school_location_license_type"
                    :multiple="true"
                    :options="$this->licenseTypes"
                    :withSearch="true"
                    placeholderText="{{ __('school_location.CLIENT') }}"
                    wire:model="filters.license_type"
                    filterContainer="school-locations-grid-active-filters"
            />
            <x-input.choices-select
                    wire:key="school_location_lvs_active"
                    :multiple="true"
                    :options="$this->yesOrNo"
                    :withSearch="true"
                    placeholderText="{{ __('school_location.lvs') }}"
                    wire:model="filters.lvs_active"
                    filterContainer="school-locations-grid-active-filters"
            />
            <x-input.choices-select
                    wire:key="school_location_sso_active"
                    :multiple="true"
                    :options="$this->yesOrNo"
                    :withSearch="true"
                    placeholderText="{{ __('school_location.sso') }}"
                    wire:model="filters.sso_active"
                    filterContainer="school-locations-grid-active-filters"
            />
            @if($this->hasActiveFilters())
                <x-button.text class="ml-auto text-base"
                                      size="sm"
                                      @click="document.getElementById('school-locations-grid-active-filters').innerHTML = '';"
                                      wire:click="clearFilters()"
                                      wire:key="clearfilters"
                >
                    <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                    <x-icon.close-small/>
                </x-button.text>
            @else
                <x-button.text class="ml-auto text-base disabled"
                                      size="sm"
                                      disabled
                                      wire:key="clearfilters-disabled"
                >
                    <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                    <x-icon.close-small/>
                </x-button.text>
            @endif
        </div>
        <div id="school-locations-grid-active-filters"
             wire:ignore
             x-data=""
             :class="($el.childElementCount !== 1) ? 'mt-2' : ''"
             class="flex flex-wrap gap-2"
        >
        </div>

    </div>

    @if (session()->has('error'))
        <div class="content-section mt-10 flex-1 p-8 error">
            {!!  session('error')  !!}
        </div>
    @endif

    <div class="content-section flex-1 p-8" x-data="{}">

        <div class="flex space-x-4 mt-4">
            <x-table>
                <x-slot name="head">
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'customer_code' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('customer_code')"
                            class="min-w-[160px]"
                    >
                        {{ __('school_location.customer_code') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('name')">
                        {{ __('school_location.school') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_name')"
                            class="min-w-[170px]"
                    >
                        {{ __('school_location.umbrella_school') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'main_city' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('main_city')">
                        {{ __('school_location.main_city') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'external_main_code' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('external_main_code')">
                        BRIN
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'lvs_active' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('lvs_active')">
                        LVS
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'sso_active' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('sso_active')">
                        SSO
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'count_questions' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('count_questions')"
                            width="170px">
                        {{ __('school_location.question_items') }}
                    </x-table.heading>
                    <x-table.heading
                            width="{{ $administrator ? '100px' : '75px' }}">
                        &nbsp;
                    </x-table.heading>

                </x-slot>
                <x-slot name="body">

                    @foreach($this->schoolLocations as $schoolLocation)

                        <x-table.row>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->customer_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ optional($schoolLocation->school)->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->main_city }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->external_main_code . ' ' . $schoolLocation->external_sub_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->lvs_active ? __('general.yes') : __('general.no') }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->sso_active ? __('general.yes') : __('general.no') }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->count_questions }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :button-cell="true">
                                @if($administrator)
                                    <x-dropdown :chevron="false" style="z-index: unset !important;" class="px-2 mx-0">
                                        <x-slot name="label">
                                            <x-icon.options/>
                                        </x-slot>
                                        <x-dropdown.item wire:click="editSchoolLocation('{{$schoolLocation->uuid}}')">
                                            <div class="flex items-center space-x-2">
                                                <x-icon.edit/>
                                                <span>{{__('school_location.edit')}}</span>
                                            </div>
                                        </x-dropdown.item>
                                        <x-dropdown.item wire:click="deleteSchoolLocation('{{$schoolLocation->uuid}}')" @click="open = false">
                                            <div class="flex items-center space-x-2">
                                                <x-icon.remove/>
                                                <span>{{__('school_location.delete')}}</span>
                                            </div>
                                        </x-dropdown.item>
                                    </x-dropdown>
                                @endif
                                <x-button.text size="sm"
                                    wire:click="viewSchoolLocation('{{$schoolLocation->uuid}}')">
                                    <span>Open</span>
                                </x-button.text>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>
        </div>
    </div>
        <div class="pt-2">
            {{ $this->schoolLocations->links('components.partials.tc-paginator') }}
        </div>
</div>