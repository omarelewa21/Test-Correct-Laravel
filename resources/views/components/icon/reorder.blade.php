<svg  {{ $attributes->merge(['class' => 'cursor-pointer ']) }} width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
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
    <g filter="" transform="translate(-916 -395)" stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round">
        <path stroke-width="2" d="M917 401h14M917 405h14"/>
        <path d="m926 408.5-2 2-2-2M926 397.5l-2-2-2 2"/>
    </g>
</svg>
