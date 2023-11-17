<div class="flex-1 py-4 mx-8" id="schools-grid" x-data="">
    <div class="flex flex-1 justify-between">
        <div><h1>{{ __('school.schools') }}</h1></div>
        <div class="flex-shrink-0">
            @if($administrator)
                <x-button.cta class="" wire:click="addNewSchool">
                    <span>{{ __('school.new-school') }}</span>
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
                                  wire:model="filters.combined_admin_grid_search"
                    />
                    <x-icon.search class="absolute right-0 -top-2"/>
                </x-input.group>
            </div>
        </div>
        <div class="flex flex-wrap w-full gap-2 mt-2">

            @if($this->hasActiveFilters())
                <x-button.text class="ml-auto text-base"
                                      size="sm"
                                      @click="document.getElementById('schools-grid-active-filters').innerHTML = '';"
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
        <div id="schools-grid-active-filters"
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
                            wire:click="setOrderByColumnAndDirection('customer_code')">
                        {{ __('school.customer_code') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('name')">
                        {{ __('school.school') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'umbrella_organization_name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('umbrella_organization_name')">
                        {{ __('school.organization') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'main_city' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('main_city')">
                        {{ __('school.main_city') }}
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'external_main_code' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('external_main_code')">
                        BRIN
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'count_questions' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('count_questions')"
                            width="155px">
                        {{ __('school.question_items') }}
                    </x-table.heading>
                    <x-table.heading
                            width="{{ $administrator ? '100px' : '75px' }}">
                        &nbsp;
                    </x-table.heading>

                </x-slot>
                <x-slot name="body">

                    @foreach($this->schools as $school)

                        <x-table.row>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $school->customer_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $school->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ optional($school->umbrellaOrganization)->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $school->main_city }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $school->external_main_code . ' ' . $school->external_sub_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $school->count_questions }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :button-cell="true">
                                @if($administrator)
                                    <x-dropdown :chevron="false" style="z-index: unset !important;" class="px-2 mx-0">
                                        <x-slot name="label">
                                            <x-icon.options/>
                                        </x-slot>
                                        <x-dropdown.item wire:click="editSchool('{{$school->uuid}}')">
                                            <div class="flex items-center space-x-2">
                                                <x-icon.edit/>
                                                <span>{{__('school.edit')}}</span>
                                            </div>
                                        </x-dropdown.item>
                                        <x-dropdown.item wire:click="deleteSchool('{{$school->uuid}}')" @click="open = false">
                                            <div class="flex items-center space-x-2">
                                                <x-icon.remove/>
                                                <span>{{__('school.delete')}}</span>
                                            </div>
                                        </x-dropdown.item>
                                    </x-dropdown>
                                @endif
                                <x-button.text size="sm"
                                    wire:click="viewSchool('{{$school->uuid}}')">
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
            {{ $this->schools->links('components.partials.tc-paginator') }}
        </div>
</div>