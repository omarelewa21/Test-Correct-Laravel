<span {{ $attributes->merge([
        'class' => 'rounded-full text-sm flex items-center justify-center border-sysbase border-3 relative px-1.5 transition-colors',
        'style' => 'min-width: 30px; height: 30px'
     ]) }}
>
    <span class="mt-px question-number bold ">{{ $slot }}</span>
</span>