<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary']) }}
        wire:key="questioncard-{{ $test->uuid }}"
>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-clamp-2 min-h-[64px] @if(blank($test->name)) italic @endif" title="{{ $test->name }}">{!! $test->id !!} {{ $test->name ? $test->name : __('test.test_name') }}</h3>

        <x-icon.options class="text-sysbase"/>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $test->subject->name }}</span>
            <span>{{ $test->abbreviation }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{__('Laatst gewijzigd') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $test->authorsAsString }}</span>
        </div>

        <x-input.custom-checkbox wire:click="handleCheckboxClick({{ $test->getKey() }})"
                                 wire:key="checkbox-for-question{{ $test->uuid }}"
                                 :checked="false"
        />
    </div>
</div>