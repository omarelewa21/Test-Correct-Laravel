@props([
        'color'
])
<span @class([
        "rounded-full w-6 h-6 border border-2 border-system-base flex items-center justify-center",
        "bg-cyan-500" => $color === '1',
        "bg-green-500" => $color === '2',
        "bg-fuchsia-500" => $color === '3',
        "bg-amber-500" => $color === '4',
        "bg-rose-500" => $color === '5',
        "bg-teal-500" => $color === '6',
        "bg-white" => $color === '7',
        "bg-cta" => $color === 'cta',
        "bg-all-red" => $color === 'all-red',
        "bg-teacher-primary-light" => $color === 'teacher-primary-light',
        "bg-orange" => $color === 'orange',
        "bg-red-400" => $color === 'red',
              ])
>
    {{ $slot }}
</span>
