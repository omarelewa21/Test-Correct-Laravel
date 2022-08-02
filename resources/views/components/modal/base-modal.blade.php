<div class="flex flex-col pb-5 pt-8 px-5 sm:px-10 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full">
    <div class="flex justify-between items-center">
        {{ $title }}
        <x-button.close wire:click="$emit('closeModal')" class="relative -right-3"/>
    </div>

    <div class="divider mb-5 mt-2.5"></div>

    <div class="body1 mb-5">
        {{ $content }}
    </div>

    {{ $footer }}
</div>
