{{--maxToolTipWidth 24rem = 24*16 = 384 px--}}
@props([
    'alwaysLeft' => false,
])

<div x-data="{tooltip: false, maxToolTipWidth: 384, alwaysLeft: @js($alwaysLeft) }"
     x-init="
        $watch('tooltip', value => {
            if (tooltip) {
                let pPos = $el.getBoundingClientRect().left;
                if (pPos + (maxToolTipWidth / 2) > window.innerWidth) {
                    $refs.tooltipdiv.classList.remove('left-1/2', '-translate-x-1/2');
                    $refs.tooltipdiv.classList.add('right-0');
                }
                if (alwaysLeft) {
                    $refs.tooltipdiv.classList.remove('left-1/2', '-translate-x-1/2');
                    $refs.tooltipdiv.classList.add('right-0');
               }
            }
        })
     "
     @mouseover="tooltip = true"
     @mouseleave="tooltip = false"
     {{ $attributes->merge(['class' => 'relative']) }}
>
    {{ $slot }}
    <div x-show="tooltip"
         x-ref="tooltipdiv"
         x-cloak
         class="absolute max-w-sm w-max bg-off-white rounded-10 p-6 main-shadow z-50 flex top-8 left-1/2 -translate-x-1/2"
    >
        {{ $text }}
    </div>
</div>
