<div class="w-full" x-data="">
    <div class="px-8 pt-4">
        <div class="flex">
            <h1>{{ __('navigation.test_files') }}</h1>
        </div>
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
                <div class="flex flex-wrap w-full gap-2 items-end" x-cloak>
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
                    <x-input.choices-select :multiple="true"
                                            :options="$this->handlers"
                                            :withSearch="true"
                                            placeholderText="{{ __('general.Behandelaar') }}"
                                            wire:model="filters.handlerid"
                                            wire:key="handlers"
                                            filterContainer="testuploads-active-filters"
                    />
                    <x-input.choices-select :multiple="true"
                                            :options="$this->testBuilders"
                                            :withSearch="true"
                                            placeholderText="{{ __('general.Toetsenbakker') }}"
                                            wire:model="filters.test_builders"
                                            wire:key="handlers"
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
                 class="flex flex-wrap gap-2 mt-2 relative transition-all"
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
                    {{--1--}}<x-table.heading width=""
                                              :sortable="true"
                                              :direction="$this->sortField == 'status' ? $this->sortDirection : null"
                                              wire:click="sortBy('status')"
                            >
                                {{ __('general.status') }}
                            </x-table.heading>
                    {{--2--}}<x-table.heading width=""
                                              :sortable="true"
                                              :direction="$this->sortField == 'planned_at' ? $this->sortDirection : null"
                                              wire:click="sortBy('planned_at')"
                            >
                                 {{ __('general.afname') }}
                             </x-table.heading>
                    {{--3--}}<x-table.heading width=""
                                              :sortable="true"
                                              :direction="$this->sortField == 'school_location_name' ? $this->sortDirection : null"
                                              wire:click="sortBy('school_location_name')"
                             >
                                 {{ __('school.school') }}
                             </x-table.heading>
                    {{--4--}}<x-table.heading width="">{{ __('auth.Docent') }}</x-table.heading>
                    {{--5--}}<x-table.heading width="">{{ __('teacher.subject') }}</x-table.heading>
                    {{--6--}}<x-table.heading width="" class="capitalize">{{ __('test.toets') }}</x-table.heading>
                    {{--7--}}<x-table.heading width="">{{ __('general.Behandelaar') }}</x-table.heading>
                    {{--8--}}<x-table.heading width="80px"
                                              :sortable="true"
                                              :direction="$this->sortField == 'test_builder' ? $this->sortDirection : null"
                                              wire:click="sortBy('test_builder')"
                             >
                                 {{ __('general.Toetsenbakker') }}
                             </x-table.heading>
                </x-slot>
                <x-slot name="body">
                    @foreach($this->testUploads as $file)
                        <x-table.row class="cursor-pointer" wire:click="openDetail('{{ $file->uuid }}')">
                            {{--1--}}<x-table.cell :slim="true" title="{{ $file->status->name }}">
                                        <span class="inline-flex relative top-0.5 rounded-sm border border-sysbase filemanagement-status label w-4 h-4"
                                              style="--active-status-color: var(--{{ $file->status->colorcode }})"></span>
                                        {{ $file->status->name }}
                                    </x-table.cell>
                            {{--2--}}<x-table.cell :slim="true" title="{{ $file->display_date }}">{{ $file->display_date }}</x-table.cell>
                            {{--3--}}<x-table.cell :slim="true" title="{{ $file->schoolLocation->name }}">{{ $file->schoolLocation->name }}</x-table.cell>
                            {{--4--}}<x-table.cell :slim="true" title="{{ $file->teacher?->name_full }}">{{ $file->teacher?->name_full }}</x-table.cell>
                            {{--5--}}<x-table.cell :slim="true" title="{{ $file->subject_name }}">{{ $file->subject_name }}</x-table.cell>
                            {{--6--}}<x-table.cell :slim="true" title="{{ $file->test_name }}">{{ $file->test_name }}</x-table.cell>
                            {{--7--}}<x-table.cell :slim="true" title="{{ $file->handler?->name_full }}">{{ $file->handler?->name_full }}</x-table.cell>
                            {{--8--}}<x-table.cell :slim="true" title="{{ $file->test_builder_code ?? '-' }}">{{ $file->test_builder_code ?? '-' }}</x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>
        </div>
        <div>
            {{ $this->testUploads->links('components.partials.tc-paginator') }}
        </div>
    </div>
</div>
