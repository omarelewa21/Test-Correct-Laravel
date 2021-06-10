@props(['active' => 'planned'])
<div class="flex w-full mx-4 lg:mx-8 xl:mx-12 max-w-7xl space-x-4">
    <div class="py-2 @if($active === 'planned') border-b-2 border-system-base border-primary-hover @endif">
        <x-button.text-button type="link"
                              href="{{ route('student.test-takes', ['tab' => 'planned']) }}">{{ __('student.planned') }}</x-button.text-button>
    </div>
    <div class="py-2 @if($active === 'discuss') border-b-2 border-system-base border-primary-hover @endif">
        <x-button.text-button type="link"
                              href="{{ route('student.test-takes', ['tab' => 'discuss']) }}">{{ __('student.discuss') }}</x-button.text-button>
    </div>
    <div class="py-2 @if($active === 'review') border-b-2 border-system-base border-primary-hover @endif">
        <x-button.text-button type="link"
                              href="{{ route('student.test-takes', ['tab' => 'review']) }}">{{ __('student.review') }}</x-button.text-button>
    </div>
    <div class="py-2 @if($active === 'graded') border-b-2 border-system-base border-primary-hover @endif">
        <x-button.text-button type="link"
                              href="{{ route('student.test-takes', ['tab' => 'graded']) }}">{{ __('student.graded') }}</x-button.text-button>
    </div>
</div>