<div
        {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary']) }}
     wire:key="questioncard-{{ $test->uuid }}"
        @click="activateCard($el)"
>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-clamp-2 min-h-[64px] text-inherit @if(blank($test->name)) italic @endif"
            title="{{ $test->name }}"
            style="color:inherit"
        >{{ $test->name ? $test->name : __('test.test_name') }}</h3>
        <div class="relative" x-data="{
                testOptionMenu: false,
                makePDF: async function(uuid) {
                    let response = await $wire.getTemporaryLoginToPdfForTest(uuid);
                    window.open(response, '_blank');
                },
                duplicateTest: async function(uuid) {
                    let response = await $wire.duplicateTest(uuid);
                    Notify.notify(response);
                }

                }">
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
                 @click="testOptionMenu=false"
            >
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        wire:click='$emit("openModal","teacher.planning-modal", {{ json_encode(["testUuid" => $test->uuid]) }})'

                >
                    <x-icon.schedule/>
                    <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
                </button>
                @if(in_array($this->openTab, ['school', 'personal']) && $test->canCopy(auth()->user())  )
                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        @click="duplicateTest('{{ $test->uuid }}')"


                >
                    <x-icon.copy/>
                    <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
                </button>
                @endif
                @if($this->openTab == 'organization' &&  $test->canCopyFromSchool(auth()->user()) )
                    <button
                            class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                            wire:click="$emitTo('teacher.copy-test-from-schoollocation-modal', 'showModal', '{{ $test->uuid }}')"
                    >
                        <x-icon.copy/>
                        <span class="text-base bold inherit">{{ __('cms.Kopie maken') }}</span>
                    </button>
                @endif
                <button
                        class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        {{--                                        @click="$dispatch('delete-modal', ['question'])"--}}
                        @click="makePDF('{{ $test->uuid }}')"
                >
                    <x-icon.pdf color="var(--system-base)"/>
                    <span class="text-base bold inherit">{{ __('cms.PDF maken') }}</span>
                </button>
                <button
                        class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        @click="window.open('{{ route('teacher.test-preview', ['test'=> $test->uuid]) }}', '_blank')"
                >
                    <x-icon.preview/>
                    <span class="text-base bold inherit">{{ __('cms.voorbeeld') }}</span>
                </button>
                @if( $test->canEdit(auth()->user()))
                    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                            wire:click="openEdit('{{ $test->uuid }}')"
                    >
                        <x-icon.edit/>
                        <span class="text-base bold inherit">{{ __('cms.Construeren') }}</span>
                    </button>
                @endif
                @if( $test->canEdit(auth()->user()))
                    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                            wire:click="$emit('openModal', 'teacher.test-edit-modal', ['testUuid' => '{{ $test->uuid }}'])"
                    >
                        <x-icon.settings/>
                        <span class="text-base bold inherit">{{ __('cms.Instellingen') }}</span>
                    </button>
                @endif
                @if( $test->canEdit(auth()->user()))
                    <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                            @click="$dispatch('delete-modal', ['question'])" --}}

                    >
                        <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                                wire:click="$emitTo('teacher.test-delete-modal', 'displayModal', '{{ $test->uuid }}')"

                        >
                            <x-icon.remove/>
                            <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                        </button>
                    </button>
                @endif
            </div>
        </div>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $test->subject->name }}</span>
            <span class="italic">{{ $test->abbreviation }}</span>
        </div>
        <div class="text-sm">
            <span class="note">{{__('Laatst gewijzigd') }}:</span>
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