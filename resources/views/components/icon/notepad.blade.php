<svg {{ $attributes }} width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter x="-1.7%" y="-5.6%" width="103.4%" height="111.1%" filterUnits="objectBoundingBox" id="a">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#a)" transform="translate(-40 -242)" stroke="currentColor" fill="none" fill-rule="evenodd" stroke-linecap="round">
        <g transform="translate(40 242)">
            <rect stroke-width="2" x="3" y="1" width="12" height="14" rx="1"/>
            <path d="M6.5 4.5h5M6.5 6.5h5M6.5 8.5h5M6.5 10.5h5"/>
            <path stroke-width="3" d="M1.5 3.5h2M1.5 7.5h2M1.5 11.5h2"/>
        </g>
    </g>
</svg>
