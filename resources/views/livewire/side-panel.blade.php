<section class="relative z-40 isolate"
         aria-labelledby="slide-over-title"
         aria-modal="true"
         x-data="sidePanel(@entangle('openSidePanel')) "
         x-show="openSidePanel"
         x-cloak
         x-ref="dialog"
         x-on:keydown.window.escape="openSidePanel = false"
>
    <div class="fixed inset-0 bg-midgrey/75 transition-colors"
         x-show="openSidePanel"
         x-cloak
         x-transition:enter="ease-in-out duration-300 "
         x-transition:enter-start="bg-midgrey/0"
         x-transition:enter-end="bg-midgrey/75"
         x-transition:leave="ease-in-out duration-300 "
         x-transition:leave-start="bg-midgrey/75"
         x-transition:leave-end="bg-midgrey/0"
    ></div>

    <div class="fixed inset-0 overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 left-0 flex max-w-full w-full pr-10"
                 x-bind:style="{top: $el.dataset.top + 'px'}"
                 data-top="@js($sidePanelAttributes['offsetTop'])"
            >

                <div x-show="openSidePanel"
                     x-cloak
                     x-transition:enter="transform transition ease-in-out duration-500"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0 delay-100"
                     x-transition:leave="transform transition ease-in-out duration-500"
                     x-transition:leave-start="translate-x-0 delay-100"
                     x-transition:leave-end="-translate-x-full"
                     x-on:click.away="openSidePanel = false"
                        @class(["pointer-events-auto w-full"])
                >
                    <div @class(["flex h-full flex-col overflow-y-scroll bg-lightGrey shadow-xl"])>
                        <article class="relative flex-1 min-w-full" wire:ignore wire:key="component-{{ md5($component) }}"  >
                            @if ($component)
                                @livewire($component, $componentAttributes)
                            @else
                                <div class="absolute inset-0 px-4 sm:px-6">
                                    <div class="h-full border-2 border-dashed border-gray-200" aria-hidden="true"></div>
                                </div>
                            @endif
                        </article>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>