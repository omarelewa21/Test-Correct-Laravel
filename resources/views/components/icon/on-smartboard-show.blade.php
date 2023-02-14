@props(['title' => ''])
<svg {{$attributes}} xmlns="http://www.w3.org/2000/svg" width="18" height="16">
    <title>{{$title}}</title>
    <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round">
        <rect width="14" height="10" x="3" y="1" stroke-width="2" rx="1"/>
        <path stroke-width="2" d="M3 15h14"/>
        <path stroke-width="3" d="M7.5 11.5h5"/>
        <g stroke-width="2">
            <path d="M1 6h8M7 4l2 2-2 2"/>
        </g>
    </g>
</svg>