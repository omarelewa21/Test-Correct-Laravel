<div class="flex flex-col space-y-4">
    <div>
        <h1>{{ __('student.tests_to_discuss') }}</h1>
    </div>
    <div class="content-section p-8 relative flex" wire:init="loadTestTakesToDiscuss">
        <x-loading/>
        @if($readyToLoad)
            @if($testTakes->count() == 0)
                <p>{{ __('student.no_test_takes_to_discuss') }}</p>
            @else
                <x-table>
                    <x-slot name="head">
                        <x-table.heading width="" sortable
                                         wire:click="sortBy('tests.name')"
                                         :direction="$sortField === 'tests.name' ? $sortDirection : null">
                            {{ __('student.test') }}
                        </x-table.heading>
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
                        <x-table.heading width="130px" class="hidden lg:table-cell">
                            {{ __('general.status') }}
                        </x-table.heading>
                        <x-table.heading width="60px" textAlign="right" class="hidden lg:table-cell">
                            {{ __('student.weight') }}
                        </x-table.heading>
                        <x-table.heading width="120px">{{ __('student.type') }}</x-table.heading>
                        <x-table.heading width="150px"></x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($testTakes as $testTake)

                            <x-table.row class="cursor-pointer"
                                         wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}')"
                            >
                                <x-table.cell :withTooltip="true">{{ $testTake->test_name }}</x-table.cell>
                                <x-table.cell :withTooltip="true">{!! $testTake->subject_name !!}</x-table.cell>
                                <x-table.cell class="text-right text-sm">
                                    @if($testTake->time_start == \Carbon\Carbon::today())
                                        <span class="capitalize">{{ __('student.today') }}</span>
                                    @else
                                        <span>{{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}</span>
                                    @endif
                                </x-table.cell>
                                <x-table.cell class="hidden lg:table-cell">
                                    {!! __($this->getTestTakeStatusTranslationString($testTake)) !!}
                                </x-table.cell>
                                <x-table.cell
                                        class="text-right hidden lg:table-cell">{{ $testTake->weight }}
                                </x-table.cell>
                                <x-table.cell>
                                    <x-partials.test-take-type-label :type="$testTake->retake"/>
                                </x-table.cell>
                                <x-table.cell buttonCell class="text-right">
{{--                                    @if($testTake->test_take_status_id == \tcCore\TestTakeStatus::STATUS_DISCUSSING)--}}
                                        <x-button.cta selid="dashboard-start-take-button" wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}')">
                                            <span>{{__("student.Bespreken")}}</span>
                                        </x-button.cta>
{{--                                    @else--}}
{{--                                        <x-button.cta selid="dashboard-start-take-button" disabled>{{__("student.Bespreken")}}</x-button.cta>--}}
{{--                                    @endif--}}
                                </x-table.cell>
                            </x-table.row>
                        @endforeach
                    </x-slot>
                </x-table>
            @endif
        @endif
    </div>
    <div>
        @if($readyToLoad)
            {{ $testTakes->links('components.partials.tc-paginator') }}
        @endif
    </div>
</div>
