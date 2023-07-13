<div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2">

    <div class="flex flex-col space-y-2">
        <span>{{ __('student.subject') }}</span>
        <h6>{!! $waitingTestTake->subject_name !!}</h6>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.take_date') }}</span>
        @if($waitingTestTake->time_start == \Carbon\Carbon::today())
            <h6 class="capitalize">{{ __('student.today') }}</h6>
        @else
            <h6>{{ \Carbon\Carbon::parse($waitingTestTake->time_start)->format('d-m-Y') }}</h6>
        @endif
    </div>
    <div class="flex flex-col space-y-2">
        @if($waitingTestTake->test_take_status_id < \tcCore\TestTakeStatus::STATUS_DISCUSSED)
            <span>{{ __('student.logged_in_students') }}</span>
            <h6 x-text="activeStudents === 0 ? '' : activeStudents"></h6>
        @else
            <span>{{ __('student.to_review_until') }}</span>
            @if($waitingTestTake->show_results)
                <h6>{{ \Carbon\Carbon::parse($waitingTestTake->show_results)->format('d-m-Y H:i') }}</h6>
            @else
                <h6>{{ __('student.cant_review_anymore') }}</h6>
            @endif
        @endif
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.class(es)') }}</span>
        <h6>
            @foreach($participatingClasses as $class)
                <span>{{ $class }}@if(!$loop->last), @endif</span>
            @endforeach
        </h6>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.teacher') }}</span>
        <h6>{{ $waitingTestTake->user()->withTrashed()->first()->getFullNameWithAbbreviatedFirstName() }}</h6>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.invigilators') }}</span>
        <h6>
            <x-partials.invigilator-list
                    :invigilators="$waitingTestTake->giveAbbreviatedInvigilatorNames()"/>
        </h6>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.weight') }}</span>
        <h6>{{ $waitingTestTake->weight }}</h6>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.info') }}</span>
        @if($waitingTestTake->test_take_status_id < \tcCore\TestTakeStatus::STATUS_TAKEN)
            <x-partials.before-take-info-labels :testTake="$waitingTestTake"/>
        @else
            <x-partials.after-take-info-labels :testTake="$waitingTestTake"/>
        @endif
    </div>

</div>
