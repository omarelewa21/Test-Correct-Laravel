<div>
@if ( $this->displayMenu)
    <div
        x-ref="contextMenu"
            x-cloak
         x-data = "{
            show: @entangle('displayMenu'),
            btnId: @entangle('btnId'),
            init() {
                $nextTick(() => {
                    const rect  = document.getElementById(this.btnId).getBoundingClientRect();
                    $refs.contextMenu.style.top = rect.top +100 + 'px';
                    $refs.contextMenu.style.left = rect.left - 100 + 'px';
                    $refs.contextMenu.style.position = 'absolute';

                })
            }
             }"


         class="absolute bg-white py-2 main-shadow rounded-10 w-72 z-30 "
         @click.outside="show = false; "
         x-transition:enter="transition ease-out origin-top-right duration-200"
         x-transition:enter-start="opacity-0 transform scale-90"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition origin-top-right ease-in duration-100"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-90"
         @click="testOptionMenu=false"
    >
        <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                wire:click='$emit("openModal","teacher.planning-modal", {{ json_encode(["testUuid" => $this->test->uuid]) }})'

        >
            <x-icon.schedule/>
            <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
        </button>
        @if(in_array($this->openTab, ['school', 'personal']) && $test->canCopy(auth()->user())  )
            <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    @click="(e)=> duplicateTest('{{ $test->uuid }}')"


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
@endif
</div>
