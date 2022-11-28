@props([
'wireKey' => false
])
<svg {{ $attributes }} width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg"
     @if($wireKey)
     wire:key="{{ $wireKey }}"
     @endif
>
    <defs>
        <filter x="-1.7%" y="-3.4%" width="103.4%" height="106.9%" filterUnits="objectBoundingBox" id="a">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#a)" transform="translate(-964 -346)" stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round">
        <path d="m977.9 351.09-.817 9a1 1 0 0 1-.996.91h-8.174a1 1 0 0 1-.996-.91L966 350h0" stroke-width="2"/>
        <path d="M973.5 353.5v4M970.5 353.5v4"/>
        <path stroke-width="3" d="M965.5 349.5h13"/>
        <path d="M969.5 348.5v-1a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v1h0"/>
    </g>
</svg>
