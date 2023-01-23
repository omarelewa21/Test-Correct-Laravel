{{--maxToolTipWidth 24rem = 24*16 = 384 px--}}
@props([
    'alwaysLeft' => false,
])

<button x-data="{tooltip: false, maxToolTipWidth: 384, alwaysLeft: @js($alwaysLeft) }"
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
        x-on:click="tooltip = !tooltip"
        x-on:click.outside="tooltip = false"
        {{ $attributes->merge(['class' => 'relative rounded-full flex py-1.5 transition-colors ']) }}
        :class="tooltip ? 'bg-primary text-white px-1.5' : 'bg-system-secondary text-sysbase px-2'"
>
    <x-icon.questionmark-small x-show="!tooltip"/>
    <x-icon.close-small x-show="tooltip"/>
    <div x-show="tooltip"
         x-ref="tooltipdiv"
         x-cloak
         class="absolute max-w-sm w-max bg-off-white rounded-10 p-6 main-shadow z-50 flex top-8 left-1/2 -translate-x-1/2 text-sysbase cursor-default"
         x-on:click.stop=""
    >
        {{ $slot }}
    </div>
</button>