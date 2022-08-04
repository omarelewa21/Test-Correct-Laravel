<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer']) }}
     wire:key="questioncard-{{ $test->uuid }}"
     @click="activateCard($el)"
     wire:click="openTestDetail('{{ $test->uuid }}')"
     wire:loading.class="hidden"
     wire:target="filters,clearFilters,$set"
>
    <div class="flex w-full justify-between mb-2 align-middle">
        <h3 class="line-clamp-2 min-h-[64px] text-inherit @if(blank($test->name)) italic @endif"
            title="{{ $test->name }}"
            style="color:inherit"
        >{{ $test->name ? $test->name : __('test.test_name') }}</h3>
        <div class="relative -top-3" x-data="{ testOptionMenu: false }"
             id="test-card-options-{{ $test->uuid }}"
        >
            <button id="test{{ $test->id }}"
                    class="px-4 py-1.5 -mr-4 h-10 w-10 rounded-full hover:bg-primary/5 text-sysbase transition-all"
                    :class="{'option-menu-active !text-white hover:!text-primary': testOptionMenu }"
                    @close-menu="testOptionMenu = false"
                    @click.stop="
                            testOptionMenu=!testOptionMenu;
                            if (testOptionMenu) {
                                $dispatch('test-card-context-menu-show', {
                                    top: $el.closest('.grid-card').offsetTop,
                                    left: $el.closest('.grid-card').offsetLeft + $el.closest('.grid-card').offsetWidth,
                                    testUuid: '{{ $test->uuid }}',
                                    button: $el,
                                    openTab: '{{ $this->openTab }}'
                                })
                            } else {
                                $dispatch('test-card-context-menu-close');
                            }
                     "
            >
                <x-icon.options class=""/>
            </button>

        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $test->subject->name }}</span>
            <span class="italic">{{ $test->abbreviation }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{ __('general.Laatst gewijzigd') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $test->authorsAsStringTwo }}</span>
        </div>
        @if ($test->isCopy())
            <div class="p-1 text-sm rounded uppercase text-muted border-2 bg-light-grey border-grey-500 text-gray-500">
                {{ __('kopie') }}
            </div>
        @endif
    </div>
</div>
