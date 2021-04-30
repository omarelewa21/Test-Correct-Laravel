@props(['timeStart', 'status', 'uuid'])

@if($timeStart == \Carbon\Carbon::today())
    @if($status > \tcCore\TestTakeStatus::STATUS_TAKING_TEST)
        <x-button.cta size="sm" disabled class="disabled">{{ __('student.make') }}</x-button.cta>
    @else
        <x-button.cta size="sm" type="link" href="{{ route('student.waiting-room', ['take' => $uuid]) }}">{{ __('student.make') }}</x-button.cta>
    @endif
@else
    <span class="italic text-sm lowercase">{{ __('student.planned') }}</span>
@endif