<div>
    <div>
        <h1>{{ __('student.upcoming_tests') }}</h1>
    </div>
    <div class="content-section p-8">
        <x-table>
            <x-slot name="head">
                <x-table.heading width="">{{ __('student.test') }}</x-table.heading>
                <x-table.heading width="60px" class="hidden lg:table-cell">{{ __('student.questions') }}</x-table.heading>
                <x-table.heading width="" class="hidden xl:table-cell">{{ __('student.invigilators') }}</x-table.heading>
                <x-table.heading width="" class="hidden xl:table-cell">{{ __('student.planner') }}</x-table.heading>
                <x-table.heading width="">{{ __('student.subject') }}</x-table.heading>
                <x-table.heading width="120px" textAlign="right">{{ __('student.take_date') }}</x-table.heading>
                <x-table.heading width="60px" textAlign="right" class="hidden lg:table-cell">
                    {{ __('student.weight') }}
                </x-table.heading>
                <x-table.heading width="100px">{{ __('student.type') }}</x-table.heading>
                <x-table.heading width="125px"></x-table.heading>
            </x-slot>
            <x-slot name="body">
                @foreach($testTakes as $testTake)

                    <x-table.row>
                        <x-table.cell>{{ $testTake->test->name }}</x-table.cell>
                        <x-table.cell
                                class="text-right hidden lg:table-cell">{{ $testTake->test->question_count }}
                        </x-table.cell>
                        <x-table.cell class="hidden xl:table-cell">
                            <x-partials.invigilator-list
                                    :invigilators="$this->giveInvigilatorNamesFor($testTake)"/>
                        </x-table.cell>
                        <x-table.cell class="hidden xl:table-cell">
                            {{ $testTake->user->getFullNameWithAbbreviatedFirstName() }}
                        </x-table.cell>
                        <x-table.cell>{!! $testTake->test->subject->name !!}</x-table.cell>
                        <x-table.cell class="text-right">
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
                            <x-partials.test-take-type-label type="{{ $testTake->retake }}"/>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            <x-partials.start-take-button :timeStart=" $testTake->time_start "
                                                          :status="$testTake->test_take_status_id"
                                                          uuid="{{$testTake->uuid}}"/>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-slot>
        </x-table>
    </div>
    <div>
        {{ $testTakes->links('components.partials.tc-paginator') }}
    </div>
</div>
