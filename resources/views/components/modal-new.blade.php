@props(['formAction' => false])


<div
        class="flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform transition-all sm:w-full"
>
    @if($formAction)
        <form wire:submit.prevent="{{ $formAction }}"> @endif
            <div class="px-2.5">
                <h2>
                    <div class="flex justify-between">
                        <span>{{ $title }}</span>
                        <span wire:click="$emit('closeModal')" class="cursor-pointer">x</span>
                    </div>
                </h2>
            </div>
            <div class="divider mb-5 mt-2.5"></div>
            <div class="px-2.5 body1 mb-5">
                {{ $body }}
            </div>
            {{ $footer }}
            @if($formAction)
        </form>
    @endif
</div>

