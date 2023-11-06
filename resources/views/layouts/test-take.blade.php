<div id="test-take-page"
     class="flex flex-col w-full"
     x-data="testTakePage"
     x-on:keyup.escape="testCodePopup = false"
>
    <div class="breadcrumbs | flex w-full border-b border-secondary sticky z-1 sticky-pseudo-bg  h-[50px] items-center">
        <div class="w-full mx-auto px-[2rem] lg:px-[90px]  z-1">
            <div class="flex w-full justify-between">
                <div class="flex items-center gap-2.5 w-full">
                    <x-button.back-round class="shrink-0 card-shadow" wire:click="back" />

                    <div class="flex text-lg bold w-[calc(100%-50px)]">
                        <span class="truncate gap-2" selid="test-detail-title">
                            <x-button.default
                                    wire:click="redirectToOverview()"
                            >{{ $this->breadcrumbTitle() }}</x-button.default>
                            <x-icon.chevron-small opacity="1" />
                            <span>{!! $this->testTake->test->name !!}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="test-info | w-full mx-auto px-[2rem] lg:px-[90px]  border-b border-secondary bg-gradient-to-t from-white to-lightGrey"
         style="--tw-gradient-to: #F0F2F5 80%;--tw-gradient-from: #fff -20%;"
    >
        <div class="w-full">
            <div class="grid gap-4 grid-cols-2 md:grid-cols-3 lg:grid-cols-4 body2 py-10">
                @foreach($this->gridData as $data)
                    <div class="flex flex-col gap-1">
                        <span>{!! $data['title'] !!}</span>
                        <h6>{!! $data['data'] !!}</h6>
                    </div>
                @endforeach
                @hasSection('gridData')
                    @yield('gridData')
                @endif
            </div>

            <div class="flex w-full items-center gap-4">
                <div class="divider flex flex-1"></div>
                @hasSection('cta')
                    @yield('cta')
                @endif
                <div class="divider flex flex-1"></div>
            </div>
            <div @class([
                  'flex w-full justify-center -mt-5 pointer-events-none',
                  'opacity-50' => $this->testTake->time_start->lt(now())
                  ])>
                <x-illustrations.waiting-room />
            </div>
        </div>
    </div>

    <div class="page-content | flex flex-col w-full mx-auto px-[2rem] lg:px-[90px]  py-10 gap-8">
        <div class="flex flex-col gap-6">
            @yield('grade-change-notification')
            <div class="flex flex-wrap">
                @hasSection('studentNames')
                    @yield('studentNames')
                @endif
                <div class="flex flex-row-reverse flex-wrap gap-2 items-start ml-auto">
                    {{-- Use the 'order-' class to sort the buttons in the correct order --}}
                    @hasSection('action-buttons')
                        @yield('action-buttons')
                    @endif
                    <livewire:actions.test-make-pdf :uuid="$this->testTake->test->uuid"
                                                    :test-take="$this->testTake->uuid"
                                                    :wire:key="'make-pdf-'.$this->testTake->uuid"
                                                    class="order-4"
                    />

                    @if($this->testTake->testTakeCode)
                        <x-button.student x-on:click="testCodePopup = true" class="order-2 px-4">
                            <span>{{ $this->testTake->testTakeCode->displayCode }}</span>
                            <x-icon.screen-expand />
                        </x-button.student>
                    @endif
                </div>
            </div>
        </div>

        @hasSection('waitingRoom')
            @yield('waitingRoom')
        @endif

        @hasSection('norming')
            @yield('norming')
        @endif

        @hasSection('results')
            @yield('results')
        @endif
    </div>

    @if($this->testTake->testTakeCode)
        <div class="test-code-popup | fixed bg-student rounded-10 top-[75px] right-12 z-10 flex items-center px-4 py-2 gap-4 justify-center"
             x-show="testCodePopup"
             x-cloak
             x-transition
             x-on:click.escape.outside="testCodePopup = false"
             style="box-shadow: 0 3px 6px 0 rgba(var(--system-base-rgb), 0.2);"
        >
            <span class="bold text-[64px] leading-none">{{ $this->testTake->testTakeCode->displayCode }}</span>
            <x-icon.copy-url class="hover:text-primary transition-colors cursor-pointer"
                             x-clipboard.raw="{{ $this->testTake->directLink }}"
                             x-on:click="urlCopied = true"
            />
            <x-icon.close-small class="self-start mt-2 cursor-pointer hover:text-primary transition-colors"
                                x-on:click="testCodePopup = false"
            />
            <div x-show="urlCopied"
                 x-transition
                 class="absolute -top-4 text-sm bold px-2 py-0.5 bg-white/80 border border-secondary rounded"
                 style="box-shadow:0 3px 3px 0 rgba(4, 31, 116, 0.1);"
            >
                <span>@lang('test-take.toets URL gekopieerd')</span>
            </div>
        </div>

        <livewire:teacher.test-take.test-participant-details-popup />
    @endif
</div>

