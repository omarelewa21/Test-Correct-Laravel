<div id="test-take-page"
     class="flex flex-col w-full"
>
    <div class="breadcrumbs | flex w-full border-b border-secondary sticky z-1 sticky-pseudo-bg before:bg-white/50 bg-transparent h-[50px] items-center">
        <div class="w-full mx-auto px-[90px] z-1">
            <div class="flex w-full justify-between">
                <div class="flex items-center gap-2.5 w-full">
                    <x-button.back-round class="shrink-0" wire:click="back" />

                    <div class="flex text-lg bold w-[calc(100%-50px)]">
                        <span class="truncate gap-2" selid="test-detail-title">
                            <x-button.text-button size="sm"
                                                  wire:click="redirectToOverview()"
                            >@lang('header.Mijn ingeplande toetsen')</x-button.text-button>
                            <x-icon.chevron-small opacity="1" />
                            <span>< toetsnaam ></span>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="w-full mx-auto px-[90px] border-b border-secondary bg-white/50">
        <div class="w-full">
            <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2 py-10">
                @foreach($this->gridData as $data)
                    <div class="flex flex-col gap-1">
                        <span>{!! $data['title'] !!}</span>
                        <h6>{!! $data['data'] !!}</h6>
                    </div>
                @endforeach
            </div>
            <div class="flex w-full items-center gap-4">
                <div class="divider flex flex-1"></div>
                <div class="flex flex-col justify-center">
                    <span class="bold text-lg">toetsafname is niet vandaag gepland</span>
                </div>
                <div class="divider flex flex-1"></div>

            </div>
            <div class="flex w-full justify-center -mt-5">
                <x-illustrations.waiting-room />
            </div>
        </div>
    </div>

    <div class="w-full mx-auto px-[90px] py-10">
        <div class="w-full flex justify-end gap-2">
            <x-button.icon>
                <x-icon.pdf-file/>
            </x-button.icon>
            <x-button.icon>
                <x-icon.settings/>
            </x-button.icon>
            <x-button.student>
                <span>{{ $this->testTake->testTakeCode->displayCode }}</span>
                <x-icon.screen-expand/>
            </x-button.student>
            <x-button.cta>
                <span>@lang('test-take.Afnemen')</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>
    </div>


    @hasSection('kaas')
        @yield('kaas')
    @endif
</div>

