@props(['formAction' => false])


<div class="flex flex-col py-6 px-10 bg-white rounded-10 shadow-xl transform transition-all sm:w-full">
    @if($formAction)<form wire:submit.prevent="{{ $formAction }}"> @endif
            <div class="px-2.5 flex justify-between">
                <h2>{{ $title }}</h2>
                <x-button.close wire:click="$emit('closeModal')" class="relative -right-3"/>
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

