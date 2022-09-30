<div class="mt-10 flex-1 " id="school-locations-grid">
    <div class="flex flex-1 justify-between">
        <div><h1>Schoollocaties</h1></div> {{-- todo translation--}}
        <div class="flex-shrink-0">
            @if(Auth::user()->isA('Administrator'))
                {{-- link to cake, new school_location --}}
                <x-button.cta class="" wire:click="addNewSchoolLocation">{{ __('school_location.new-school-location') }}</x-button.cta> {{-- todo translation--}}
            @endif
                <x-dropdown label="{{__('school_location.filter')}}" dropdownwidth="400px">
                    <div class="grid grid-cols-2 px-4 py-2 items-center">
                        <label for="customer_code">{{ __('school_location.customer_code') }}</label>
                        <x-input.text id="customer_code" title="{{ __('school_location.customer_code') }}" wire:model="filters.customer_code"></x-input.text>
                    </div>
                    <div class="grid grid-cols-2 px-4 py-2 items-center">
                        <label for="customer_code">{{ __('school_location.school') }}</label>
                        <x-input.text id="school" title="{{ __('school_location.school') }}" wire:model="filters.name"></x-input.text>
                    </div>
                    <div class="grid grid-cols-2 px-4 py-2 items-center">
                        <label for="umbrella_school">{{ __('school_location.umbrella_school') }}</label>
                        <x-input.text id="umbrella_school" title="{{ __('school_location.umbrella_school') }}" wire:model="filters.school_name"></x-input.text>
                    </div>
                    <div class="grid grid-cols-2 px-4 py-2 items-center">
                        <label for="customer">{{ __('school_location.customer') }}</label>
                        <x-input.text id="customer" title="{{ __('school_location.customer') }}" wire:model="filters.id">
                            {{-- todo input select --}}
                        </x-input.text>
                    </div>
{{--                    <x-dropdown.item--}}
{{--                            onclick="">--}}
{{--                        gello--}}
{{--                    </x-dropdown.item>--}}
{{--                    <x-dropdown.item--}}
{{--                            onclick="">--}}
{{--                        gello2--}}
{{--                    </x-dropdown.item>--}}
                </x-dropdown>
        </div>
    </div>

    @if (session()->has('error'))
        <div class="content-section mt-10 flex-1 p-8 error">
            {!!  session('error')  !!}
        </div>
    @endif

    <div class="content-section mt-10 flex-1 p-8" x-data="{}">

        <div class="flex space-x-4 mt-4">
            <x-table>
                <x-slot name="head">
                    {{-- Klantcode | School | Gemeenschap | Stad | BRIN | LVS | SSO | Licenties | Geactiveerd | Vraagitems | (actions) --}}
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.customer_code' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.customer_code')">
                        Klantcode
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.name')">
                        School
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'schools.name' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('schools.name')">
                        Gemeenschap
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.main_city' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.main_city')">
                        Stad
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.external_main_code' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('external_main_code')">
                        BRIN
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.lvs_type' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.lvs_type')">
                        LVS
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.sso_type' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.sso_type')">
                        SSO
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.count_active_licenses' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.count_active_licenses')"
                            width="75px">
                        Licenties
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.count_students' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.count_students')"
                            width="75px">
                        Geactiveerd
                    </x-table.heading>
                    <x-table.heading
                            :sortable="true"
                            :direction="$this->orderByColumnName == 'school_locations.count_questions' ? $this->orderByDirection : null"
                            wire:click="setOrderByColumnAndDirection('school_locations.count_questions')"
                            width="75px">
                        Vraagitems
                    </x-table.heading>
                    <x-table.heading
                            width="75px">
                        &nbsp;
                    </x-table.heading>

                </x-slot>
                <x-slot name="body">

                    @foreach($this->schoolLocations as $schoolLocation)
                        @php
                            if($schoolLocation->count_active_licenses > 0) {
                                $percentage = round((100 / $schoolLocation->count_active_licenses) * $schoolLocation->count_students);
                            }else{
                                $percentage = '0';
                            }
                        @endphp


                        <x-table.row>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->customer_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->school->name }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->main_city }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->external_main_code . ' / ' . $schoolLocation->external_sub_code }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->lvs_type }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->sso_type }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->count_active_licenses }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->count_students . " (" . $percentage . "%)" }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :with-tooltip="true">
                                {{ $schoolLocation->count_questions }}
                            </x-table.cell>
                            <x-table.cell :slim="true" :button-cell="true">
                                        <x-button.text-button size="sm"
                                                wire:click="">Open {{-- todo translation--}}
                                        </x-button.text-button>
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>

        </div>
        <div class="pt-2">
            {{ $this->schoolLocations->links('components.partials.tc-paginator') }}
        </div>

        <x-slot name="footerbuttons">&nbsp;</x-slot>
        <x-slot name="testTakeManager">&nbsp;</x-slot>
    </div>
</div>

{{-- Grid current vs new: --}}
{{-- Klantcode | School | Gemeenschap | Stad | Licenties | Geactiveerd | Vraagitems | (actions) --}}
{{-- Klantcode | School | Gemeenschap | Stad | BRIN | LVS | SSO | Licenties | Geactiveerd | Vraagitems | (actions) --}}

{{-- Sort and Filter. sort all columns, filter not all--}}
{{-- Klantcode, School, Gemeenschap, Klant*, Brin*, LVS*, SSO*  *is selectbox--}}

{{-- todo: administrator can create new school on this screen, account manager can't --}}
{{-- on cake side: Popup.load('/school_locations/add', 1100); --}}

{{-- actions--}}
{{-- always (admin & account manager): folder icon button
        Navigation.load('/school_locations/view/<?=getUUID($school_location, 'get'); //REDIRECT TO VIEW PAGE IN CAKE
--}}
{{-- if administrator (in dropdown menu)
        Popup.load('/school_locations/edit/<?=getUUID($school_location, 'get');?>', 1100);  //REDIRECT TO EDIT PAGE IN CAKE
        SchoolLocation.delete(<?=getUUID($school_location, 'getQuoted');  //DELETE CAN BE DONE IN LARAVEL?
--}}