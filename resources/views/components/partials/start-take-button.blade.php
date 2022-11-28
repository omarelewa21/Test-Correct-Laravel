@props(['timeStart', 'timeEnd', 'uuid', 'isAssignment'])

@if($isAssignment)
    @if($timeStart <= now() && $timeEnd >= now())
        <x-button.cta selid="dashboard-start-take-button" size="sm" type="link"
                      href="{{ route('student.waiting-room', ['take' => $uuid]) }}">{{ __('student.make') }}</x-button.cta>
    @else
        <span class="italic text-sm lowercase">{{ __('student.planned') }}</span>
    @endif
@else
    @if($timeStart == \Carbon\Carbon::today())
        <x-button.cta selid="dashboard-start-take-button" size="sm" type="link"
                      href="{{ route('student.waiting-room', ['take' => $uuid]) }}">{{ __('student.make') }}</x-button.cta>
    @else
        <span class="italic text-sm lowercase">{{ __('student.planned') }}</span>
    @endif
@endif
