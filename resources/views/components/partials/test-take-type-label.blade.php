@props(['type' => 0])
<span class="flex justify-center text-xs uppercase bold px-2.5 py-1 rounded-[4px] {{ $type == 0 ? 'bg-system-secondary base' : 'bg-[#f0ad4e] text-white' }} ">{{ $type == 0 ? 'standaard' : 'inhaaltoets' }}</span>
