@props(['type' => 0])
<span class="flex justify-center max-w-max text-[10px] uppercase bold px-2.5 py-1 rounded-[4px] {{ $type == 0 ? 'bg-system-secondary base' : 'bg-system-base system-secondary' }} ">{{ $type == 0 ? 'standaard' : 'inhaaltoets' }}</span>
