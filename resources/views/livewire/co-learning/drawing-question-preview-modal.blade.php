<div cms id="cms-preview" class="flex flex-1 flex-col bg-lightGrey h-full overflow-auto"
>
    <div class="question-editor-preview-header flex w-full bg-white items-center pl-6 pr-3 py-4 fixed z-10">
        <div class="bold flex items-center min-w-max space-x-2.5 text-lg">
            <x-icon.preview/>
            <span>VRAAG VOORBEELD:</span>
        </div>

        <h3 class="line-clamp-1 break-all px-2.5">
            {{__('co-learning.DrawingQuestion')}}
        </h3>

        <div class="flex ml-auto items-center space-x-2.5">

            <x-button.close wire:click="$emit('closeModal')"/>
        </div>
    </div>
    <div class="pt-[70px] w-full mx-auto h-full relative" wire:ignore.self>

        <img src="{{$this->imgSrc}}"
             class="border border-blue-grey rounded-10 w-full bg-white">

    </div>
</div>

