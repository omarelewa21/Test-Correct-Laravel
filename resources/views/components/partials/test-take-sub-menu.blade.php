@props(['active' => 'planned', 'disabled' => false])
<div class="flex w-full px-4 lg:px-8 xl:px-24 menu">
    <div>
        <x-button.default @class([
                                    "px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 border-b-2 border-transparent",
                                    "text-primary border-primary" => $active === 'planned'
                                  ])
                          size="md"
                          :disabled="$disabled"
                          type="link"
                          href="{{ route('student.test-takes', ['tab' => 'planned']) }}"
        >
            <span>{{ __('student.planned') }}</span>
        </x-button.default>
    </div>
    <div>
        <x-button.default @class([
                                    "px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 border-b-2 border-transparent",
                                    "text-primary border-primary" => $active === 'discuss'
                                  ])
                          size="md"
                          :disabled="$disabled"
                          type="link"
                          href="{{ route('student.test-takes', ['tab' => 'discuss']) }}"
        >
            <span>{{ __('student.discuss') }}</span>
        </x-button.default>
    </div>
    <div>
        <x-button.default @class([
                                    "px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 border-b-2 border-transparent",
                                    "text-primary border-primary" => $active === 'review'
                                  ])
                          size="md"
                          :disabled="$disabled"
                          type="link"
                          href="{{ route('student.test-takes', ['tab' => 'review']) }}"
        >
            <span>{{ __('student.review') }}</span>
        </x-button.default>
    </div>
    <div>
        <x-button.default @class([
                                    "px-2 border-0 hover:text-primary hover:bg-primary/5 active:bg-primary/10 border-b-2 border-transparent",
                                    "text-primary border-primary" => $active === 'graded'
                                  ])
                          size="md"
                          :disabled="$disabled"
                          type="link"
                          href="{{ route('student.test-takes', ['tab' => 'graded']) }}"
        >
            <span>{{ __('student.results') }}</span>
        </x-button.default>
    </div>
</div>