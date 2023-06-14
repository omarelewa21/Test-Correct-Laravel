<div id="test-take-page"
     class="flex flex-col w-full"
     x-data="{testCodePopup: false}"
>
    <div class="breadcrumbs | flex w-full border-b border-secondary sticky z-1 sticky-pseudo-bg  h-[50px] items-center">
        <div class="w-full mx-auto px-[90px] z-1">
            <div class="flex w-full justify-between">
                <div class="flex items-center gap-2.5 w-full">
                    <x-button.back-round class="shrink-0 card-shadow" wire:click="back" />

                    <div class="flex text-lg bold w-[calc(100%-50px)]">
                        <span class="truncate gap-2" selid="test-detail-title">
                            <x-button.text-button size="sm"
                                                  wire:click="redirectToOverview()"
                            >@lang('header.Mijn ingeplande toetsen')</x-button.text-button>
                            <x-icon.chevron-small opacity="1" />
                            <span>{!! $this->testTake->test->name !!}</span>
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <div class="w-full mx-auto px-[90px] border-b border-secondary bg-gradient-to-t from-white to-lightGrey"
         style="--tw-gradient-to: #F0F2F5 80%;--tw-gradient-from: #fff -20%;"
    >
        <div class="w-full">
            @js($this->testTake->id)
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
            <div class="flex w-full justify-center -mt-5 pointer-events-none">
                <x-illustrations.waiting-room />
            </div>
        </div>
    </div>

    <div class="flex flex-col w-full mx-auto px-[90px] py-10 gap-6">
        <div class="w-full flex justify-end gap-2">
            <livewire:actions.test-make-pdf :uuid="$this->testTake->test->uuid" :wire:key="'make-pdf-'.$this->testTake->test->uuid"/>
            <x-button.icon>
                <x-icon.settings/>
            </x-button.icon>
            @if($this->testTake->testTakeCode)
                <x-button.student x-on:click="testCodePopup = !testCodePopup">
                    <span>{{ $this->testTake->testTakeCode->displayCode }}</span>
                    <x-icon.screen-expand/>
                </x-button.student>
            @endif
            <x-button.cta>
                <span>@lang('test-take.Afnemen')</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>

        @hasSection('kaas')
            @yield('kaas')
        @endif
    </div>

    @if($this->testTake->testTakeCode)
    <div class="test-code-popup | fixed bg-student rounded-10 top-[75px] right-12 z-10 flex items-center px-4 py-2"
         x-show="testCodePopup"
         x-cloak
         x-transition
         x-on:click.outside="testCodePopup = false"
    >
        <span class="bold text-[64px] leading-none">{{ $this->testTake->testTakeCode->displayCode }}</span>
        <x-icon.close-small class="self-start mt-2 cursor-pointer"
                            x-on:click="testCodePopup = false"
        />
    </div>
    @endif
</div>

