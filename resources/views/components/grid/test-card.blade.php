<div {{ $attributes->merge(['class' => 'grid-card context-menu-container']) }}
     wire:key="testcard-{{ $test->uuid }}"
     @if($mode === 'cms')
         x-on:click="showQuestionsOfTest('{{ $test->uuid }}')"
     @else
     wire:click="openTestDetail('{{ $test->uuid }}')"
     @endif
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
                                   contextDataJson="{openTab: '{{ $openTab }}', mode: '{{ $mode }}' }"
            />
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{!! $test->subject->name !!}</span>
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
        <div>
            <x-published-tag :published="$test->isPublished()"/>
        </div>
    </div>
</div>
