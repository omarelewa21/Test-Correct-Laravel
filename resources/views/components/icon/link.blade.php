<svg {{ $attributes }} width="18" height="16" viewBox="0 0 18 16" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter x="-1.7%" y="-3.6%" width="103.4%" height="107.2%" filterUnits="objectBoundingBox" id="a">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.1 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
        <filter x="-12%" y="-43.8%" width="124%" height="187.5%" filterUnits="objectBoundingBox" id="b">
            <feOffset dy="3" in="SourceAlpha" result="shadowOffsetOuter1"/>
            <feGaussianBlur stdDeviation="3" in="shadowOffsetOuter1" result="shadowBlurOuter1"/>
            <feColorMatrix values="0 0 0 0 0.0156862745 0 0 0 0 0.121568627 0 0 0 0 0.454901961 0 0 0 0.3 0" in="shadowBlurOuter1" result="shadowMatrixOuter1"/>
            <feMerge>
                <feMergeNode in="shadowMatrixOuter1"/>
                <feMergeNode in="SourceGraphic"/>
            </feMerge>
        </filter>
    </defs>
    <g filter="url(#a)" transform="translate(-855 -100)" stroke="currentColor" stroke-width="2" fill="none" fill-rule="evenodd" stroke-linecap="round">
        <g transform="translate(839 88)" filter="url(#b)">
            <path d="m18.225 22.932 1.572-1.514a2.5 2.5 0 0 1 3.468 0l.103.1a2.453 2.453 0 0 1 0 3.535l-1.573 1.514a2.5 2.5 0 0 1-3.467 0l-.103-.1a2.453 2.453 0 0 1 0-3.535zM25.498 15.932l1.573-1.514a2.5 2.5 0 0 1 3.467 0l.103.1a2.453 2.453 0 0 1 0 3.535l-1.573 1.514a2.5 2.5 0 0 1-3.467 0l-.103-.1a2.453 2.453 0 0 1 0-3.535zM21.917 22.829l5.143-4.95M29.19 15.828 32.13 13"/>
        </g>
    </g>
</svg>
