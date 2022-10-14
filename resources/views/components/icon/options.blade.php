@php
    $uniqueFilterId = \Ramsey\Uuid\Uuid::uuid4();
@endphp
<svg {{ $attributes->merge(['class' => 'overflow-visible']) }} width="4" height="16" viewBox="0 0 4 16" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter x="-1.7%" y="-3.3%" width="103.4%" height="106.6%" filterUnits="objectBoundingBox" id="{{$uniqueFilterId}}">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#{{$uniqueFilterId}})"  fill="currentColor" fill-rule="evenodd">
        <g >
            <circle cx="2" cy="2" r="2"/>
            <circle cx="2" cy="8" r="2"/>
            <circle cx="2" cy="14" r="2"/>
        </g>
    </g>
</svg>
