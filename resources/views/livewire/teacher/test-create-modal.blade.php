<x-modal-with-footer id="{{$this->modalId}}" maxWidth="3xl" :showCancelButton="false" wire:model="showModal">
    <x-slot name="title">
        <div class="flex justify-between">
            <span>{{__("teacher.toets aanmaken")}}</span>
            <span wire:click="showModal()" class="cursor-pointer">x</span>
        </div>
    </x-slot>
    <x-slot name="body">
        <div class="flex px-1">
            <div name="block-container" class="grid grid-cols-2 pt-5"></div>
        </div>

    </x-slot>
    <x-slot name="footer">
        <div class="flex justify-between w-full">
            <x-button.text-button @click="show = false" >
                <x-icon.arrow-left/>
                <span>{{ __("modal.Terug") }}</span>
            </x-button.text-button>

            <div class="absolute bottom-8 left-1/2 -translate-x-1/2 h-4 flex items-center justify-center space-x-2">
                <div class="border-0 rounded-xl bg-primary h-[14px] w-[14px]"></div>
                <div class="border-0 rounded-xl bg-bluegrey h-[14px] w-[14px]"></div>
            </div>

            <x-button.cta>
                <span>{{ __("teacher.toets aanmaken") }}</span>
                <x-icon.arrow/>
            </x-button.cta>
        </div>
    </x-slot>
</x-modal-with-footer>

