<div class="flex flex-col space-y-4">
    <div>
        <h1>{{ __('student.tests_results') }}</h1>
    </div>
    <div class="content-section p-8 relative" wire:init="loadRatings">
        <x-loading/>
        @if($readyToLoad)
            @if($testTakes->count() == 0)
                <p>{{ __('student.no_recent_results') }}</p>
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
                        <x-table.heading width="120px"></x-table.heading>
                    </x-slot>
                    <x-slot name="body">
                        @foreach($testTakes as $testTake)
                            <x-table.row class="cursor-pointer"
                                         wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}', 'graded')"
                            >
                                <x-table.cell :withTooltip="true">{!! $testTake->test_name !!}</x-table.cell>
                                <x-table.cell :withTooltip="true">{!! $testTake->subject_name !!}</x-table.cell>
                                <x-table.cell>{!! $this->getTeacherNameForRating($testTake->user_id) !!}</x-table.cell>
                                <x-table.cell class="text-right text-sm">
                                    @if($testTake->time_start == \Carbon\Carbon::today())
                                        <span class="capitalize">{{ __('student.today') }}</span>
                                    @else
                                        <span>{{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}</span>
                                    @endif
                                </x-table.cell>
                                <x-table.cell>
                                    <x-partials.test-take-type-label :type="$testTake->retake"/>
                                </x-table.cell>
                                <x-table.cell class="text-center">
                                    @if(!$testTake->show_grades)
                                        <span title="{{__('test_take.hide_grade_tooltip')}}">
                                            {{ __('test_take.nvt') }}
                                        </span>
                                    @elseif($testTake->testParticipants->first()->rating)
                                        <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForTestParticipantRating($this->getRatingToDisplay($testTake->testParticipants->first())) !!}">
                                            {{ $this->getRatingToDisplay($testTake->testParticipants->first()) }}
                                        </span>
                                    @else
                                        <span class="text-sm rounded-full bg-grade" style="background-color: #929daf">
                                            <x-icon.time-dispensation class="text-white" :title="__('test_take.waiting_grade')"/>
                                        </span>
                                    @endif
                                </x-table.cell>
                                @if($this->testTakeReviewable($testTake))
                                    <x-table.cell buttonCell class="text-right">
                                        <x-button.cta>{{ __('student.review') }}</x-button.cta>
                                    </x-table.cell>
                                @else
                                    <x-table.cell/>
                                @endif
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
