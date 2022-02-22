<svg {{ $attributes }} xmlns="http://www.w3.org/2000/svg" width="20" height="16">
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
    <g filter="url(#a)" transform="translate(-239 -257)" fill="none" fill-rule="evenodd">
        <g transform="translate(239 257)">
            <path stroke="currentColor" stroke-linejoin="round" stroke-width="2"
                  d="M10 14c3.314 0 6.314-2 9-6-2.686-4-5.686-6-9-6S3.686 4 1 8c2.686 4 5.686 6 9 6z"/>
            <circle cx="10" cy="8" r="5.5" stroke="currentColor"/>
            <path fill="currentColor"
                  d="M10 5c.1 0 .197.005.294.014C9.563 5.114 9 5.742 9 6.5c0 .828.672 1.5 1.5 1.5S12 7.328 12 6.5c0-.417-.17-.795-.445-1.067C12.42 5.96 13 6.912 13 8c0 1.657-1.343 3-3 3S7 9.657 7 8s1.343-3 3-3z"/>
            <path stroke="currentColor" stroke-linecap="round" stroke-width="2" d="M17 15L3 1"/>
        </g>
    </g>
</svg>
