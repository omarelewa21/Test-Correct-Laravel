<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary']) }}
     wire:key="questioncard-{{ $test->uuid }}"
>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-clamp-2 min-h-[64px] @if(blank($test->name)) italic @endif"
            title="{{ $test->name }}">{!! $test->id !!} {{ $test->name ? $test->name : __('test.test_name') }}</h3>
        <div class="relative" x-data="{testOptionMenu: false}">
            <button class="px-4 py-1.5 -mr-4 rounded-full hover:bg-primary hover:text-white transition-all"
                    :class="{'bg-primary text-white' : testOptionMenu === true}"
                    @click="testOptionMenu = true">
                <x-icon.options class="text-sysbase"/>
            </button>
            <div x-cloak
                 x-show="testOptionMenu"
                 class="absolute right-0 top-10 bg-white py-2 main-shadow rounded-10 w-72 z-30 "
                 @click.outside="testOptionMenu=false"
                 x-transition:enter="transition ease-out origin-top-right duration-200"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition origin-top-right ease-in duration-100"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
            >
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.schedule/>
                    <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
                </button>
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.schedule/>
                    <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
                </button>
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.pdf/>
                    <span class="text-base bold inherit">{{ __('cms.PDF maken') }}</span>
                </button>
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.preview/>
                    <span class="text-base bold inherit">{{ __('cms.voorbeeld') }}</span>
                </button>
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.edit/>
                    <span class="text-base bold inherit">{{ __('cms.Wijzigen') }}</span>
                </button>
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}

                >
                    <x-icon.remove/>
                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                </button>
            </div>
        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $test->subject->name }}</span>
            <span>{{ $test->abbreviation }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{__('Laatst gewijzigd') }}:</span>
            <span class="note">{{ Carbon\Carbon::parse($test->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $test->authorsAsString }}</span>
        </div>

        <x-input.custom-checkbox wire:click="handleCheckboxClick({{ $test->getKey() }})"
                                 wire:key="checkbox-for-question{{ $test->uuid }}"
                                 :checked="false"
        />
    </div>
</div>