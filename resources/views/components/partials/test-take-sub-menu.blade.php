@props(['active' => 'planned', 'disabled' => false])
<div class="flex w-full mx-4 lg:mx-8 xl:mx-12 max-w-7xl space-x-4 menu">
    <div>
        @if(!$disabled)
            <x-button.text-button class="{{ $active === 'planned' ? 'active' : '' }}" type="link" href="{{ route('student.test-takes', ['tab' => 'planned']) }}"><span>{{ __('student.planned') }}</span></x-button.text-button>
        @else
            <x-button.text-button class="{{ $active === 'planned' ? 'active' : '' }}" disabled class="cursor-pointer" type="link"><span>{{ __('student.planned') }}</span></x-button.text-button>
        @endif
    </div>
    <div>
        @if(!$disabled)
        <x-button.text-button class="{{ $active === 'discuss' ? 'active' : '' }}" type="link" href="{{ route('student.test-takes', ['tab' => 'discuss']) }}"><span>{{ __('student.discuss') }}</span></x-button.text-button>
            @else
        <x-button.text-button class="{{ $active === 'discuss' ? 'active' : '' }}" type="link" disabled class="cursor-pointer"><span>{{ __('student.discuss') }}</span></x-button.text-button>
        @endif
    </div>
    <div>
        @if(!$disabled)
        <x-button.text-button class="{{ $active === 'review' ? 'active' : '' }}" type="link" href="{{ route('student.test-takes', ['tab' => 'review']) }}"><span>{{ __('student.review') }}</span></x-button.text-button>
            @else
        <x-button.text-button class="{{ $active === 'review' ? 'active' : '' }}" type="link" disabled class="cursor-pointer"><span>{{ __('student.review') }}</span></x-button.text-button>
        @endif
    </div>
    <div>
        @if(!$disabled)
        <x-button.text-button class="{{ $active === 'graded' ? 'active' : '' }}" type="link" href="{{ route('student.test-takes', ['tab' => 'graded']) }}"><span>{{ __('student.graded') }}</span></x-button.text-button>
        @else
        <x-button.text-button class="{{ $active === 'graded' ? 'active' : '' }}" @endif type="link" disabled class="cursor-pointer"><span>{{ __('student.graded') }}</span></x-button.text-button>
        @endif
    </div>
</div>