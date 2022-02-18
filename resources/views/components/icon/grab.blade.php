<svg {{ $attributes }} xmlns="http://www.w3.org/2000/svg" width="16" height="16">
    <defs>
        <filter id="a" width="132.4%" height="113.2%" x="-13.8%" y="-5.5%"
                filterUnits="objectBoundingBox">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur in="shadowOffsetOuter1" result="shadowBlurOuter1" stdDeviation="9"/>
            <feColorMatrix in="shadowBlurOuter1" result="shadowMatrixOuter1"
                           values="0 0 0 0 0.301960784 0 0 0 0 0.341176471 0 0 0 0 0.560784314 0 0 0 0.2 0"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#a)" transform="translate(-275 -12)" stroke="currentColor" stroke-width="2"
       fill="none" fill-rule="evenodd" stroke-linecap="round">
        <path d="M276 17h14m-14 6h14"/>
    </g>
</svg>