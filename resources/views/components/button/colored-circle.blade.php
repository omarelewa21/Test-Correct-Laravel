@props([
        'color',
        'disabled' => false,
])
<span @class([
        "rounded-full w-6 h-6 border border-2 border-system-base flex items-center justify-center" ,
        "cursor-pointer" => !$disabled,
        "opacity-50" => $disabled,
        "bg-cta" => $color === 'cta',
        "bg-all-red" => $color === 'all-red',
        "bg-teacher-primary-light" => $color === 'teacher-primary-light',
        "bg-orange" => $color === 'orange',
        "bg-red-400" => $color === 'red',
      ])
        {{ $attributes->except('color') }}
        style="
        background-color: {{$color}};
        "
>
    {{ $slot }}
</span>
