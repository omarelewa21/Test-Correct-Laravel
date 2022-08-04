<div class="{{ $class ?? '' }}">
    @if($variant == 'icon-button')
    <x-button.cta wire:click="planTest"
        title="{{ __('teacher.Inplannen') }}"
    >
        <x-icon.schedule/>
        <span>{{ __('cms.Inplannen') }}</span>
    </x-button.cta>
    @elseif($variant == 'context-menu')
        <button
                class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));makePDF()"
                wire:click="planTest"
        >
            <span class="w-5 flex justify-center"><x-icon.schedule/></span>
            <span class="text-base bold inherit">{{ __('cms.Inplannen') }}</span>
        </button>
    @endif
</div>
