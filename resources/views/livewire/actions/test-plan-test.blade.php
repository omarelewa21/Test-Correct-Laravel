<div class="{{ $class ?? '' }}">
    @if($variant == 'icon-button-with-text')
        <x-button.cta wire:click="planTest"
                      title="{{ __('teacher.Inplannen') }}"
                      class="px-4"
        >
            <x-icon.schedule/>
            <span>{{ __('cms.Inplannen') }}</span>
        </x-button.cta>
    @elseif($variant == 'icon-button')
        <x-button.icon color="cta" wire:click="handle" title="{{ __('teacher.Inplannen') }}">
            <x-icon.schedule/>
        </x-button.icon>
    @elseif($variant == 'context-menu')
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                wire:click="handle"
        >
            <span class="w-5 flex justify-center"><x-icon.schedule/></span>
            <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
        </button>
    @endif
</div>
