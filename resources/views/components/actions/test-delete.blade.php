<div {{ $attributes }} x-data="{}">
    @if($variant == 'icon-button')
        @if (!$disabled)
            <x-button.primary
                    title="{{ __('teacher.Verwijderen') }}"
                    class="w-10 p-0 items-center justify-center"
                    @click="$wire.emit('openModal','teacher.test-delete-modal', {testUuid: '{{  $test->uuid }}'})">
                <x-icon.trash/>

            </x-button.primary>
        @else
            <x-button.primary
                    class="w-10 p-0 items-center justify-center opacity-20 cursor-not-allowed">
                <x-icon.trash/>
            </x-button.primary>
        @endif
    @elseif($variant == 'context-menu')
        @if( !$disabled)
            <button
                    class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                    @click="$wire.emit('openModal','teacher.test-delete-modal', {testUuid: '{{  $test->uuid }}'})"
            >
                <span class="w-5 flex justify-center"><x-icon.remove/></span>
                <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
            </button>

        @endif
    @endif
</div>
