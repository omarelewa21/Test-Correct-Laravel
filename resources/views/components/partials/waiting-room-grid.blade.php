<div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2">

    <div class="flex flex-col space-y-2">
        <span>{{ __('student.subject') }}</span>
        <h6>{!! $waitingTestTake->test->subject->name !!}</h6>
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
        <span>{{ __('student.logged_in_students') }}</span>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.clas(ses)') }}</span>
    </div>
    <div class="flex flex-col space-y-2">
        <span>{{ __('student.teacher') }}</span>
        <h6>{{ $waitingTestTake->user->getFullNameWithAbbreviatedFirstName() }}</h6>
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
        <span>{{ __('student.type') }}</span>
        <x-partials.test-take-type-label type="{{ $waitingTestTake->retake }}"/>
    </div>

</div>