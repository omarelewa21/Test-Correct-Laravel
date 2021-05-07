@props(['timeStart', 'uuid'])

@if($timeStart == \Carbon\Carbon::today())
    <x-button.cta size="sm" type="link"
                  href="{{ route('student.waiting-room', ['take' => $uuid]) }}">{{ __('student.make') }}</x-button.cta>
@else
    <span class="italic text-sm lowercase">{{ __('student.planned') }}</span>
@endif