<svg {{ $attributes }} width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter x="-1.7%" y="-3.3%" width="103.4%" height="106.6%" filterUnits="objectBoundingBox" id="a">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#a)" transform="translate(-272 -100)" fill="none" fill-rule="evenodd">
        <path d="m276 105 3.486-2.091a1 1 0 0 1 1.514.857v8.468a1 1 0 0 1-1.514.857L276 111h-3a1 1 0 0 1-1-1v-4a1 1 0 0 1 1-1h3z" fill="currentColor"/>
        <path d="M285 113.01a6.419 6.419 0 0 0 2.397-5.008c0-2.02-.932-3.822-2.388-5.001M283 111.01a3.854 3.854 0 0 0 1.439-3.006c0-1.213-.56-2.295-1.434-3.003" stroke="currentColor" stroke-linecap="round"/>
    </g>
</svg>
