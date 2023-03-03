<header id="header"
        @class([
            'maintenance-header-bg' => $hasActiveMaintenance,
            'deployment-testing-marker' => $isOnDeploymentTesting,
            'h-[var(--header-height)]' => $this->headerCollapsed,
            'h-full' => ! $this->headerCollapsed,
        ])
        x-data="{
            isCollapsed: @js($this->headerCollapsed),
            collapseHeader: false,
            handleHeaderCollapse: async function( args ){
                result = await $wire.handleHeaderCollapse(args);
                if(result !== false) {
                    this.isCollapsed = true;
                }
            }
        }"

        @unless($this->headerCollapsed)
            x-show="!isCollapsed"
            x-collapse.min.70px.duration.1500ms
        @endif
>
    <div class="py-2.5 px-6 flex h-[var(--header-height)] items-center justify-between">
        <div class="flex items-center space-x-4">
            <x-button.back-round wire:click="redirectBack()" class="bg-white/20 hover:text-white"></x-button.back-round>
            {{ $title }}
        </div>
        <div class="flex">
            @if($this->headerCollapsed)
                {{ $collapsedLeft }}
            @endif
        </div>
    </div>
    @unless($this->headerCollapsed)
        <div id="start-screen-content" class="h-full flex justify-center items-center">
            <div class="flex flex-col mb-[110px]">
                <div class="flex items-center justify-center h-8 mb-4">
                    <h3 class="text-center text-white">{{ $subtitle ?? 'Kies je sessie methode' }}</h3>
                </div>
                <div class="grid grid-cols-2 gap-5">
                    {{ $panels }}
                </div>
            </div>
        </div>
    @endif
</header>