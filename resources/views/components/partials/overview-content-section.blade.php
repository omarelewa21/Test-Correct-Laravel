<div class="flex flex-col pt-4 pb-16" style="min-height: 500px" >
    <div class="flex justify-between">
        <span class="note text-sm" wire:loading
              wire:target="filters,clearFilters,$set">{{  __('general.searching') }}</span>

        <span class="note text-sm"
              selid="resultStats"
              wire:loading.remove
              wire:target="filters,clearFilters,$set">
            @isset($resultMessage) {{ $resultMessage }} @endisset()
            </span>

        @isset($header)
            {{ $header }}
        @endisset
    </div>
    <x-grid class="my-4" selid="resultsContainer">
        @foreach(range(1, 6) as $value)
            <x-grid.loading-card
                    :delay="$value"
                    wire:loading.class.remove="hidden"
                    wire:target="filters,clearFilters,$set"
            />
        @endforeach

        {{ $cards }}

    </x-grid>
    @if($pagination)
    {{ $results->links('components.partials.tc-paginator') }}
    @endif
    {{ $slot }}
</div>
