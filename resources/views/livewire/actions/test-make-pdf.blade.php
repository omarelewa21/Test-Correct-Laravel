<div class="{{ $class ?? '' }}" x-data="{
 makePDF: async function() {
                let response = await $wire.getTemporaryLoginToPdfForTest();
                window.open(response, '_blank');
            }
}">
    @if($variant == 'icon-button')
            <x-button.primary
                class="pl-[12px] pr-[12px] "
                @click="makePDF()"
                title="{{ __('teacher.PDF maken') }}"
            >
                <x-icon.pdf-file color="var(--off-white)"/>
            </x-button.primary>
            <x-slot name="text">
                                <span class="text-base text-left">
                                    {{ __('teacher.Toets PDF-weergave') }}
                                </span>
            </x-slot>
    @elseif($variant == 'context-menu')
        <button
            class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
            @click="$event.target.dispatchEvent(new CustomEvent('context-menu-close', { bubbles: true }));makePDF()"
        >
            <span class="w-5 flex justify-center"><x-icon.pdf-file /></span>
            <span class="text-base bold inherit">{{ __('cms.PDF maken') }}</span>
        </button>
    @endif
</div>
