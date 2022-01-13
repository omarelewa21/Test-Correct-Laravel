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
    <g filter="url(#a)" transform="translate(-517 -100)" fill="none" fill-rule="evenodd">
        <path d="M518.77 101.799A24.996 24.996 0 0 1 525 101c2.077 0 4.154.266 6.23.799a2 2 0 0 1 1.489 1.69 36.4 36.4 0 0 1 0 9.022 2 2 0 0 1-1.488 1.69A24.996 24.996 0 0 1 525 115c-2.077 0-4.154-.266-6.23-.799a2 2 0 0 1-1.489-1.69 36.4 36.4 0 0 1 0-9.022 2 2 0 0 1 1.488-1.69z" fill="#CF1B04"/>
        <path d="m523.752 104.939 4.508 2.63a.5.5 0 0 1 0 .863l-4.508 2.63a.5.5 0 0 1-.752-.433v-5.258a.5.5 0 0 1 .752-.432z" fill="#FFF"/>
    </g>
</svg>
