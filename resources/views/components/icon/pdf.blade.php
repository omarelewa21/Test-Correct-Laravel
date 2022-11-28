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
    <g filter="url(#a)" transform="translate(-49 -100)" stroke="{{ $color ?? '#CF1B04'}}" fill="none" fill-rule="evenodd">
        <path d="M56.148 100.5c2.64 0 .845 3.78-1.372 8.188-2.216 4.41-3.694 7.664-4.855 6.614-1.16-1.05-.21-2.835 6.544-4.724 6.755-1.89 8.55-.735 7.916.42-.633 1.155-4.75.315-6.966-3.045-2.216-3.359-3.905-7.453-1.267-7.453z"/>
    </g>
</svg>
