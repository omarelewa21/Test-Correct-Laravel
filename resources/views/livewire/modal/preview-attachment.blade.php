<x-partials.modal.preview>
    @switch($this->attachmentType)
        @case('video')
            <iframe class="w-full h-full" src="{{ $this->attachment->getVideoLink() }}"></iframe>
            @break
        @case('pdf')
            <iframe class="w-full h-full"
                    src="{{ route('teacher.preview.question-pdf-attachment-show', ['attachment' => $this->attachment->uuid, 'question' => $this->questionUuid]) }}"></iframe>
            @break
        @case('audio')


            <div class="flex flex-col w-full h-full justify-center items-center bg-white space-y-3 rounded-10"
                x-init="$nextTick(() => {
                    controls = ['play', 'progress', 'current-time', 'mute', 'volume'];
                    player = plyrPlayer.renderWithoutConstraints($refs.player);
                })"
            >
                <div class="w-3/4">
                    <div class="mt-4" wire:ignore>
                        <audio id="player" src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $this->attachment->uuid, 'question' => $this->questionUuid]) }}"
                               x-ref="player"
                        ></audio>
                    </div>
                </div>
            </div>


            @break
        @case('image')
            <div class="w-full h-full"
                 x-data="{
         zoomOut: () => {
                if(this.percentage <= 12.5) { return; }
                if(this.percentage <= 25) {
                    this.percentage = 12.5;
                } else {
                    this.percentage = this.percentage - 25;
                }
                previewImage.style.width = this.percentage.toString() + '%';
             },
             zoomIn: () => {
                 this.percentage = this.percentage + 25;
                 previewImage.style.width = this.percentage.toString() + '%';
             }
         }"
                 x-init="previewImage = $refs.imagePreview;
                 percentage = 100;
                 previewImage.style.width = percentage.toString() + '%';
         ">
                <div class="w-full h-full overflow-auto flex flex-col items-center align-center justify-center">
                    <img src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionUuid]) }}" style="max-width: 300%"
                         class="w-full bg-white" alt="Preview image"
                         x-ref="imagePreview">
                </div>
                <div style="position: absolute;  bottom: 19px; right: 67px;"
                     @click="zoomIn()"
                >
                    <x-button.icon-circle>
                        <x-icon.plus/>
                    </x-button.icon-circle>
                </div>
                <div style="position: absolute; bottom: 19px; right: 19px;"
                     @click="zoomOut()"
                >
                    <x-button.icon-circle>
                        <x-icon.min/>
                    </x-button.icon-circle>
                </div>
            </div>
            @break
        @default
            <div class="w-full h-full">
                Not available...
            </div>
    @endswitch


    <x-slot name="icon">
        <x-dynamic-component :component="$iconComponentName"/>
    </x-slot>
</x-partials.modal.preview>