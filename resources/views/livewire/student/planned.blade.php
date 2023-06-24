<div class="flex flex-col space-y-4">
    <div>
        <h1>{{ __('student.upcoming_tests') }}</h1>
    </div>
    <div class="content-section p-8 relative">
        <x-loading/>
        @if($testTakes->count() == 0)
            <p>{{ __('student.no_upcoming_tests') }}</p>
        @else
            <x-table>
                <x-slot name="head">
                    <x-table.heading width="" sortable
                                     wire:click="sortBy('tests.name')"
                                     :direction="$sortField === 'tests.name' ? $sortDirection : null">
                        {{ __('student.test') }}
                    </x-table.heading>
                    <x-table.heading width="60px"
                                     class="hidden lg:table-cell">{{ __('student.questions') }}</x-table.heading>
                    <x-table.heading width="150px"
                                     class="hidden xl:table-cell">{{ __('student.invigilators') }}</x-table.heading>
                    <x-table.heading width="150px"
                                     class="hidden xl:table-cell">{{ __('student.planner') }}</x-table.heading>
                    <x-table.heading width="150px"
                                     sortable
                                     wire:click="sortBy('subjects.name')"
                                     :direction="$sortField === 'subjects.name' ? $sortDirection : null">
                        {{ __('student.subject') }}
                    </x-table.heading>

                    <x-table.heading width="105px" sortable wire:click="sortBy('test_takes.time_start')"
                                     :direction="$sortField === 'test_takes.time_start' ? $sortDirection : null"
                                     textAlign="right">
                        {{ __('student.take_date') }}
                    </x-table.heading>

                    <x-table.heading width="60px" textAlign="right" class="hidden lg:table-cell">
                        {{ __('student.weight') }}
                    </x-table.heading>
                    <x-table.heading width="120px">{{ __('student.type') }}</x-table.heading>
                    <x-table.heading width="125px"></x-table.heading>
                </x-slot>
                <x-slot name="body">
                    @foreach($testTakes as $testTake)

                        <x-table.row class="cursor-pointer"
                                     wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}')">
                            <x-table.cell :withTooltip="true">{{ $testTake->test_name }}</x-table.cell>
                            <x-table.cell :withTooltip="true"
                                    class="text-right hidden lg:table-cell">{{ $testTake->question_count }}
                            </x-table.cell>
                            <x-table.cell class="hidden xl:table-cell">
                                <x-partials.invigilator-list
                                        :invigilators="$testTake->giveAbbreviatedInvigilatorNames()"/>
                            </x-table.cell>
                            <x-table.cell class="hidden xl:table-cell">
                                {{ $testTake->user()->withTrashed()->first()->getFullNameWithAbbreviatedFirstName() }}
                            </x-table.cell>
                            <x-table.cell :withTooltip="true">{!! $testTake->subject_name !!}</x-table.cell>
                            <x-table.cell class="text-right text-sm">
                                @if($testTake->time_start == \Carbon\Carbon::today())
                                    <span class="capitalize">{{ __('student.today') }}</span>
                                @else
                                    <span>{{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}</span>
                                @endif
                            </x-table.cell>
                            <x-table.cell
                                    class="text-right hidden lg:table-cell">{{ $testTake->weight }}
                            </x-table.cell>
                            <x-table.cell>
                                <x-partials.before-take-info-labels :$testTake />
                            </x-table.cell>
                            <x-table.cell buttonCell class="text-right">
                                <x-partials.start-take-button :timeStart="$testTake->time_start"
                                                              :timeEnd="$testTake->time_end"
                                                              :uuid="$testTake->uuid"
                                                              :isAssignment="$testTake->is_assignment"
                                />
                            </x-table.cell>
                        </x-table.row>
                    @endforeach
                </x-slot>
            </x-table>
        @endif
    </div>
    <div>
        {{ $testTakes->links('components.partials.tc-paginator') }}
    </div>
</div>
