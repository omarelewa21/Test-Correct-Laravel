@extends('livewire.modal.preview-attachment')

@section('content')
    <div class="flex flex-col w-full h-full justify-center items-center bg-white space-y-3 rounded-10"
         x-init="$nextTick(() => {
                    controls = ['play', 'progress', 'current-time', 'mute', 'volume'];
                    player = plyrPlayer.renderWithoutConstraints($refs.player);
                })"
    >
        <div class="w-3/4">
            <div class="mt-4" wire:ignore>
                <audio id="player"
                       src="{{ $this->source }}"
                       x-ref="player"
                ></audio>
            </div>
        </div>
    </div>
@endsection
