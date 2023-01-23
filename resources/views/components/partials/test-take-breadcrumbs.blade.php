@props(['step' => 1])

<div {{ $attributes->merge(['class' => 'flex body2 bold items-center space-x-2']) }}>
    <div class="flex items-center space-x-2">
        <x-icon.schedule/>
        <span>{{ __('student.planned') }}</span>
    </div>
    <div class="flex items-center space-x-2 @if($step < 2) opacity-50 @endif">
        <x-icon.chevron-small/>
        <x-icon.discuss/>
        <span>{{ __('student.discuss') }}</span></div>
    <div class="flex items-center space-x-2 @if($step < 3) opacity-50 @endif">
        <x-icon.chevron-small/>
        <x-icon.preview/>
        <span>{{ __('student.review') }}</span></div>
    <div class="flex items-center space-x-2 @if($step < 4) opacity-50 @endif">
        <x-icon.chevron-small/>
        <x-icon.grade/>
        <span>{{ __('student.results') }}</span></div>
</div>