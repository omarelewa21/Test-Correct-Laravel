<div class="flex mx-6 items-center h-10 justify-between flex-shrink-0"
     id="student-info-container"
>
    @php
        $i = rand(0,4);
    @endphp
    {{-- left --}}
    <div class="flex items-center h-full space-x-1">
        <span class="min-w-[1rem] w-4 flex items-center justify-center">
            @if($i === 0)
                <x-icon.time-dispensation class="text-orange"/>
            @elseif($i === 1)
                <x-icon.warning class="text-red-500"/>
            @else
                <x-icon.checkmark-small class="text-cta"/>
            @endif
        </span>
        <span class="min-w-[1rem] w-4 flex items-center justify-center">
            @if($i === 0)
                <x-icon.smiley-normal class="text-midgrey"/>
            @elseif($i === 1)
                <x-icon.smiley-normal class="text-orange"/>
            @elseif($i === 2)
                <x-icon.smiley-sad class="text-red-500"/>
            @elseif($i === 3)
                <x-icon.smiley-default class="text-midgrey"/>
            @else
                <x-icon.smiley-happy class="text-cta"/>
            @endif
        </span>
        <span class="student-name">{{$userFullName}}</span>
    </div>
    {{-- right --}}
    <div class="show-on-smartboard relative" wire:click.prevent="showStudentAnswer('uuid or id')">
        <x-icon.on-smartboard-show />
    </div>
</div>