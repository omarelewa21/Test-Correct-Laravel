<svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
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
    <g filter="url(#a)" transform="translate(-49 -148)" fill="none" fill-rule="evenodd">
        <g transform="translate(49 148)">
            <path stroke="currentColor" stroke-width="2" stroke-linejoin="round" d="M1 1h14v14H1z"/>
            <circle fill="currentColor" cx="4.5" cy="5.5" r="1.5"/>
            <path d="M13.014 6.767A4.48 4.48 0 0 0 10.5 6c-1.27 0-1.968.242-3.234 1.37-1.011.903-2.81 1.403-4.258.804C3.003 8.39 3 9.667 3 12h10c0-2.333.005-4.078.014-5.233z" fill="currentColor"/>
        </g>
    </g>
</svg>
