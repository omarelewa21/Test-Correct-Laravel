<div wire:loading.flex
     wire:target="{{ $attributes->get('model') }}"
     wire:ignore
     id="dummy"
     class="animate-pulse border rounded-lg border-blue-grey items-center mr-4 mb-2 ">
    <div class="flex p-2 border-r border-blue-grey h-full items-center">
        <x-icon.attachment/>
    </div>
    <div class="flex base items-center relative">
        <span class="p-2 text-base max-w-[200px] truncate"></span>
    </div>
</div>