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
            doneCollapsing: @js($this->headerCollapsed),
            handleHeaderCollapse: async function( args ){
                result = await $wire.handleHeaderCollapse(args);
                if(result !== false) {
                    this.isCollapsed = true;
                    setTimeout(() => this.doneCollapsing = true, 1500)
                }
            },
            redirectBack: function() {
                if(this.$store.answerFeedback.feedbackBeingEdited()) {
                    return this.$store.answerFeedback.openConfirmationModal(this.$root, 'redirectBack');
                }
                $wire.redirectBack();
            },
        }"
        x-on:continue-navigation="Alpine.$data($el)[$event.detail.method]()"

        @unless($this->headerCollapsed)
            x-show="!isCollapsed"
        x-collapse.min.70px.duration.1500ms
        @endif
>
    <div class="py-2.5 px-6 flex h-[var(--header-height)] items-center justify-between  @hasSection('set-up-colearning') border-bottom-05-secondary @endif"

         >
        <div class="flex items-center space-x-4 truncate">
            <x-button.back-round x-on:click="redirectBack()"
                                 background-class="bg-white/20"
                                 class="hover:text-white"
                                 title="{{ $backButtonTitle }}"
            />
            @yield('title')
        </div>
        <div class="flex device-dependent-margin" x-show="doneCollapsing" x-transition x-cloak>
            @if($this->headerCollapsed)
                @hasSection('collapsedLeft')
                    @yield('collapsedLeft')
                @endif
            @endif
        </div>
    </div>
    @unless($this->headerCollapsed)
        <div id="start-screen-content" class="h-[calc(100vh-70px)] flex justify-center overflow-y-auto">
        @hasSection('set-up-colearning')
            <div class="flex flex-col w-full">
                @yield('set-up-colearning')
            </div>
        @else
            <div class="flex flex-col mb-[110px]">
                <div class="flex flex-col gap-2 items-center justify-center mb-4">
                    <h3 class="text-center text-white">
                        @hasSection('subtitle')
                            @yield('subtitle')
                        @endif
                    </h3>
                    @yield('notification-box')
                </div>
                <div class="flex flex-wrap justify-center">
                    @yield('panels')
                </div>
                @hasSection('additionalInfo')
                    @yield('additionalInfo')
                @endif
            </div>

        @endif
        </div>
    @endif
</header>