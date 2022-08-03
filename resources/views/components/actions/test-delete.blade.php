<div x-data="{}">
    @if($variant == 'icon-button')
        @if ($test->canDelete(auth()->user()))

            <x-button.primary
                    title="{{ __('teacher.Verwijderen') }}"
                    class="pl-[12px] pr-[12px]"
                    @click="$wire.emitTo('teacher.test-delete-modal', 'displayModal', '{{  $test->uuid }}')">
                <x-icon.trash/>

            </x-button.primary>
                <x-slot name="text">
                                <span class="text-base text-left">
                                    {{ __('teacher.Verwijder test') }}
                                </span>
                </x-slot>

        @else
            <x-button.primary
                    class="pl-[12px] pr-[12px] opacity-20 cursor-not-allowed">
                <x-icon.trash/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if( $test->canDelete(auth()->user()))
            <button
                    class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));$wire.emitTo('teacher.test-delete-modal', 'displayModal', '{{  $test->uuid }}')"
            >
                <span class="w-5 flex justify-center"><x-icon.remove/></span>
                <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
            </button>

        @endif
    @endif
</div>
