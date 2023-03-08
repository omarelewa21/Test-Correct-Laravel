{{--maxToolTipWidth 24rem = 24*16 = 384 px--}}
@props([
    'alwaysLeft' => false,
    'activeClasses' => 'bg-primary text-white ',
    'idleClasses' => 'bg-system-secondary text-sysbase',
])
<button x-data="{
            tooltip: false,
            maxToolTipWidth: 384,
            alwaysLeft: @js($alwaysLeft),

            }"
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
        x-cloak
        x-on:click="tooltip = !tooltip"
        x-on:click.outside="tooltip = false"
        x-bind:class="tooltip ? @js($activeClasses) : @js($idleClasses)"
        @class([
          $attributes->get('class'),
          'relative flex w-[22px] h-[22px] items-center justify-center rounded-full transition-colors'
        ])
>
    <x-icon.questionmark-small x-show="!tooltip" x-cloak/>
    <x-icon.close-small x-show="tooltip" x-cloak/>
    <div x-show="tooltip"
         x-ref="tooltipdiv"
         x-cloak
         class="absolute max-w-sm w-max bg-off-white rounded-10 p-6 main-shadow z-50 flex top-8 left-1/2 -translate-x-1/2 text-sysbase cursor-default"
         x-on:click.stop=""
    >
        {{ $slot }}
    </div>
</button>