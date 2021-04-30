@if($invigilators->count() > 1)
    <div x-data="{open: false}">
        <div class="relative" @mouseover="open = true" @mouseleave="open = false">
            {{ $invigilators->first() }}, +{{ $invigilators->count()-1 }}
        </div>
        <div
                x-cloak
                x-show.transition="open"
                id="popover"
                class="p-3 space-y-1 max-w-xl bg-white rounded shadow-2xl flex flex-col text-sm text-gray-600 mt-3 absolute z-20">
            {{ $invigilators->implode(', ')}}
        </div>
    </div>
@else
    {{ $invigilators->first() }}
@endif