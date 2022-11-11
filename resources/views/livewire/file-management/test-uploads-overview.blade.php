<div class="w-full" x-data>
    <div class="flex flex-col py-4 border-b border-secondary">
        <div class="flex w-full my-2">
            <div class="relative w-full">
                <x-input.text class="w-full search"
                              placeholder="{{ __('cms.Search...') }}"
                              wire:model.debounce.300ms="filters.search"
                />
                <x-icon.search class="absolute right-0 -top-2"/>
            </div>
        </div>
        <div class="flex w-full items-center">
            <div class="flex flex-wrap w-full space-x-2 items-end" x-cloak>
                <x-input.choices-select :multiple="true"
                                        :options="$this->teachers"
                                        :withSearch="true"
                                        placeholderText="{{ __('auth.Docent') }}"
                                        wire:model="filters.teacherid"
                                        wire:key="teachers"
                                        filterContainer="testuploads-active-filters"
                />
                <x-input.choices-select :multiple="true"
                                        :options="$this->statusses"
                                        :withSearch="false"
                                        placeholderText="{{ __('general.status') }}"
                                        wire:model="filters.status_ids"
                                        wire:key="statusses"
                                        filterContainer="testuploads-active-filters"
                />
                <x-input.choices-select :multiple="true"
                                        :options="$this->baseSubjects"
                                        :withSearch="true"
                                        placeholderText="{{ __('general.Categorie') }}"
                                        wire:model="filters.base_subjects"
                                        wire:key="base_subjects"
                                        filterContainer="testuploads-active-filters"
                />
                <span class="bg-system-secondary  w-px h-10"></span>
                <div class="flex flex-col gap-y-1">
                    <span class="pl-1">Afnamedatum</span>
                    <div>
                        <x-input.datepicker class="bg-offwhite w-[170px]"
                                            wire:model="filters.planned_at_start"
                                            locale="{{ app()->getLocale() }}"
                                            placeholder="{{ __('general.van') }}"/>

                        <x-input.datepicker class="bg-offwhite w-[170px]"
                                            wire:model="filters.planned_at_end"
                                            locale="{{ app()->getLocale() }}"
                                            placeholder="{{ __('general.tot') }}"/>
                    </div>
                </div>
            </div>

            <x-button.text-button class="ml-auto text-base"
                                  size="sm"
                                  wire:click="clearFilters()"
                                  :disabled="!$this->hasActiveFilters()"
            >
                <span class="min-w-max">{{ __('teacher.Filters wissen') }}</span>
                <x-icon.close-small/>
            </x-button.text-button>
        </div>

        <div id="testuploads-active-filters"
             x-data
             wire:key="filters-container"
             wire:ignore
             class="flex flex-wrap gap-2 mt-2 relative"
        >
        </div>
    </div>

    <div class="my-2">
        <span class="note text-sm" wire:loading
              wire:target="filters,clearFilters,$set">{{  __('general.searching') }}</span>

        <span class="note text-sm"
              wire:loading.remove
              wire:target="filters,clearFilters,$set"
        >
            {{ trans_choice('general.number-of-results', $this->testUploads->total(), ['count' => $this->testUploads->total()]) }}
        </span>
    </div>

    <div class="content-section p-8 relative isolate">
        <x-loading/>
        <x-table>
            <x-slot name="head">
                {{--1--}}<x-table.heading width="">Status</x-table.heading>
                {{--2--}}<x-table.heading width="">Afname</x-table.heading>
                {{--3--}}<x-table.heading width="">School</x-table.heading>
                {{--4--}}<x-table.heading width="">Docent</x-table.heading>
                {{--5--}}<x-table.heading width="">Vak</x-table.heading>
                {{--6--}}<x-table.heading width="">Toets</x-table.heading>
                {{--7--}}<x-table.heading width="">Behandelaar</x-table.heading>
                {{--8--}}<x-table.heading width="80px">Toetsenbakker</x-table.heading>
            </x-slot>
            <x-slot name="body">
                @foreach($this->testUploads as $file)
                    <x-table.row class="cursor-pointer" wire:click="openDetail('{{ $file->uuid }}')">
                        {{--1--}}<x-table.cell><x-file-management-status :status="$file->status"/></x-table.cell>
                        {{--2--}}<x-table.cell title="{{ $file->display_date }}">{{ $file->display_date }}</x-table.cell>
                        {{--3--}}<x-table.cell title="{{ $file->schoolLocation->name }}">{{ $file->schoolLocation->name }}</x-table.cell>
                        {{--4--}}<x-table.cell title="{{ $file->teacher?->name_full }}">{{ $file->teacher?->name_full }}</x-table.cell>
                        {{--5--}}<x-table.cell title="{{ $file->subject_name }}">{{ $file->subject_name }}</x-table.cell>
                        {{--6--}}<x-table.cell title="{{ $file->test_name }}">{{ $file->test_name }}</x-table.cell>
                        {{--7--}}<x-table.cell title="{{ $file->handler?->name_full }}">{{ $file->handler?->name_full }}</x-table.cell>
                        {{--8--}}<x-table.cell title="{{ $file->test_builder_code ?? '-' }}">{{ $file->test_builder_code ?? '-' }}</x-table.cell>
                    </x-table.row>
                @endforeach
            </x-slot>
        </x-table>
    </div>
    <div>
        {{ $this->testUploads->links('components.partials.tc-paginator') }}
    </div>
</div>
