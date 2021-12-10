<div class="flex flex-col space-y-4">
    <div>
        <h1>{{ __('student.graded_tests') }}</h1>
    </div>
    <div class="content-section p-8 relative" wire:init="loadRatings">
        <x-loading />
        @if($readyToLoad)
            @if($testParticipants->count() == 0)
                <p>{{ __('student.no_recent_grades') }}</p>
            @else
                <x-table>
                    <x-slot name="head">
                        <x-table.heading width=""
                                         sortable
                                         wire:click="sortBy('tests.name')"
                                         :direction="$sortField === 'tests.name' ? $sortDirection : null"
                        >
                            {{ __('student.test') }}
                        </x-table.heading>
                        <x-table.heading width="150px"
                                         sortable
                                         wire:click="sortBy('subjects.name')"
                                         :direction="$sortField === 'subjects.name' ? $sortDirection : null">
                            {{ __('student.subject') }}
                        </x-table.heading>
                        <x-table.heading width="180px">{{ __('student.teacher') }}</x-table.heading>
                        <x-table.heading width="105px"
                                         textAlign="right"
                                         sortable
                                         wire:click="sortBy('test_takes.time_start')"
                                         :direction="$sortField === 'test_takes.time_start' ? $sortDirection : null"
                        >
                            {{ __('student.take_date') }}
                        </x-table.heading>
                        <x-table.heading width="120px">{{ __('student.type') }}</x-table.heading>
                        <x-table.heading width="70px">{{ __('student.grade') }}</x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($testParticipants as $testParticipant)
                            <x-table.row class="cursor-pointer"
                                         wire:click="redirectToWaitingRoom('{!!$testParticipant->test_take_uuid !!}')"
                            >
                                <x-table.cell :withTooltip="true">{!! $testParticipant->name !!}</x-table.cell>
                                <x-table.cell :withTooltip="true">{!! $testParticipant->subject_name !!}</x-table.cell>
                                <x-table.cell>{!! $this->getTeacherNameForRating($testParticipant->user_id) !!}</x-table.cell>
                                <x-table.cell class="text-right text-sm">
                                    @if($testParticipant->time_start == \Carbon\Carbon::today())
                                        <span class="capitalize">{{ __('student.today') }}</span>
                                    @else
                                        <span>{{ \Carbon\Carbon::parse($testParticipant->time_start)->format('d-m-Y') }}</span>
                                    @endif
                                </x-table.cell>
                                <x-table.cell>
                                    <x-partials.test-take-type-label type="{{ $testParticipant->retake }}"/>
                                </x-table.cell>
                                <x-table.cell class="text-right">
                                        <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForTestParticipantRating($this->getRatingToDisplay($testParticipant)) !!}">
                                            {{ $this->getRatingToDisplay($testParticipant) }}
                                        </span>
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
            {{ $testParticipants->links('components.partials.tc-paginator') }}
        @endif
    </div>
</div>