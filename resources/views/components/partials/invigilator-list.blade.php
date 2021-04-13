@if($invigilators->count() > 1)
    <div x-data="{open: false}">
        <style>
            #popover:before{
                content: "";
                position: absolute;
                bottom: 100%;
                left: 5%;
                margin-left: -10px;
                border-width: 7px;
                border-style: solid;
                border-color: transparent transparent lightgray transparent;
            }
        </style>
        <div class="relative" @mouseover="open = true" @mouseleave="open = false">
            {{ $invigilators->first() }}, +{{ $invigilators->count()-1 }}
        </div>
        <div
                x-cloak
                x-show.transition="open"
                id="popover"
                class="p-3 space-y-1 max-w-xl bg-white rounded shadow-2xl flex flex-col text-sm text-gray-600 mt-3 absolute z-20">
            {{implode(PHP_EOL,$invigilators->toArray())}}
        </div>
    </div>
@else
    {{ $invigilators->first() }}
@endif