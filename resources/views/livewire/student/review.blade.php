<div class="flex flex-col space-y-4">
    <div>
        <h1>{{ __('student.tests_to_review') }}</h1>
    </div>
    <div class="content-section p-8 relative" wire:init="loadTestTakesToReview">
        <x-loading/>
        @if($readyToLoad)
            @if($testTakes->count() == 0)
                <p>{{ __('student.no_test_takes_to_review') }}</p>
            @else
                <x-table>
                    <x-slot name="head">
                        <x-table.heading width="" sortable
                                         wire:click="sortBy('tests.name')"
                                         :direction="$sortField === 'tests.name' ? $sortDirection : null">
                            {{ __('student.test') }}
                        </x-table.heading>
                        <x-table.heading width=""
                                         sortable
                                         wire:click="sortBy('subjects.name')"
                                         :direction="$sortField === 'subjects.name' ? $sortDirection : null">
                            {{ __('student.subject') }}
                        </x-table.heading>
                        <x-table.heading width="250px">
                            {{ __('student.invigilator_note') }}
                        </x-table.heading>
                        <x-table.heading width="130px" sortable wire:click="sortBy('test_takes.time_start')"
                                         :direction="$sortField === 'test_takes.time_start' ? $sortDirection : null"
                                         textAlign="right">
                            {{ __('student.take_date') }}
                        </x-table.heading>
                        <x-table.heading width="200px"
                                         textAlign="right">{{ __('student.review_until') }}</x-table.heading>
                        <x-table.heading width="125px"></x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($testTakes as $testTake)

                            <x-table.row class="cursor-pointer"
                                         wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}')"
                            >
                                <x-table.cell>{{ $testTake->test_name }}</x-table.cell>
                                <x-table.cell>{!! $testTake->subject_name !!}</x-table.cell>
                                <x-table.cell class="hidden lg:table-cell">
                                    {!! $testTake->participant_invigilator_note !!}
                                </x-table.cell>
                                <x-table.cell class="text-right">
                                    @if($testTake->time_start == \Carbon\Carbon::today())
                                        <span class="capitalize">{{ __('student.today') }}</span>
                                    @else
                                        <span>{{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}</span>
                                    @endif
                                </x-table.cell>

                                <x-table.cell class="text-right">
                                    <span>{{ \Carbon\Carbon::parse($testTake->show_results)->format('d-m-Y H:i') }}</span>
                                </x-table.cell>
                                <x-table.cell buttonCell class="text-right">
                                    <x-button.cta>{{ __('student.review') }}</x-button.cta>
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
