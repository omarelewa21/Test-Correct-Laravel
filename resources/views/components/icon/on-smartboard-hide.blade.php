@props(['title' => ''])
<svg {{$attributes}} xmlns="http://www.w3.org/2000/svg" width="20" height="16">
    <title>{{$title}}</title>
    <g fill="none" fill-rule="evenodd" stroke="currentColor" stroke-linecap="round">
        <rect width="13" height="10" x="6" y="1" stroke-width="2" rx="1"/>
        <path stroke-width="2" d="M6 15h13"/>
        <path stroke-width="3" d="M10.5 11.5h4"/>
        <g stroke-width="2">
            <path d="M9 6H1M3 4 1 6l2 2"/>
        </g>
    </g>
</svg>