<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer']) }}
     wire:key="questioncard-{{ $test->uuid }}"
     wire:click="openTestDetail('{{ $test->uuid }}')"
     wire:loading.class="hidden"
     wire:target="filters,clearFilters,$set"
>
    <div class="flex w-full justify-between mb-2 align-middle">
        <h3 class="line-clamp-2 word-break-words min-h-[64px] text-inherit @if(blank($test->name)) italic @endif"
            title="{{ $test->name }}"
            style="color:inherit"
        >{{ $test->name ? $test->name : __('test.test_name') }}</h3>
            <x-button.options id="test{{ $test->id }}"
                                   context="test-card"
                                   :uuid="$test->uuid"
                                   contextDataJson="{openTab: '{{ $this->openTab }}' }"
            />
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $test->subject->name }}</span>
            <span class="italic">{{ $test->abbreviation }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{ __('general.Laatst gewijzigd') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $test->authorsAsStringTwo }}</span>
        </div>
        @if ($test->isCopy())
            <div class="p-1 text-sm rounded uppercase text-muted border-2 bg-light-grey border-grey-500 text-gray-500">
                {{ __('kopie') }}
            </div>
        @endif
    </div>
</div>
