@if($attachment)
    <div id="attachment-{{$attachment->uuid}}"
         class="fixed z-30 shadow-lg border border-blue-grey rounded-10 bg-black disable-swipe-navigation {{ $this->getAttachmentModalSize() }}"
         x-data="{top: @entangle('positionTop'), left: @entangle('positionLeft'), uuid: '{{ $attachment->uuid }}'}"
         x-init="top = 50; left = 50"
         x-bind:style="`top: ${top}px; left: ${left}px`"
         x-on:set-new-position.window="
            if (uuid === $event.detail.uuid) {
                top = $event.detail.x;
                left = $event.detail.y;
            }
        "
    >
        <div class="hidden h-[45vw] w-3/4 h-1/2 w-5/6 lg:w-4/6 h-[80vh] w-[80vw] h-[45vw]"></div>
        <div class="flex-col relative h-full rounded-10">
            <div class="flex absolute top-0 right-0 justify-end space-x-2 z-10" style="-webkit-transform: translateZ(10px);">
                <x-button.secondary id="attachment-{{$attachment->uuid}}drag" class="rotate-svg-45">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </x-button.secondary>
                @if($this->attachmentType == 'audio')
                    <x-button.primary wire:click="$emit('clickPauseButtonAndCloseModal')">
                        <x-icon.close class="text-white"/>
                    </x-button.primary>
                @else
                    <x-button.primary wire:click="closeAttachmentModal">
                        <x-icon.close class="text-white"/>
                    </x-button.primary>
                @endif
            </div>
            <div class="flex w-full h-full rounded-10 attachment-iframe-wrapper @if($this->attachmentType == 'image') max-h-[80vh] @endif">
                @if($this->attachmentType == 'video')
                    <iframe class="w-full h-full" src="{{ $attachment->getVideoLink() }}"></iframe>
                @elseif($this->attachmentType == 'pdf')
                    <iframe class="w-full h-full"
                            src="{{ route('student.question-pdf-attachment-show', ['attachment' => $attachment, 'answer' => $answerId], false) }}"></iframe>
                @elseif($this->attachmentType == 'audio')
                    <x-attachment.audio :attachment="$attachment" />
                @else
                        <img class="w-full h-full block"
                            src="{{ route('student.question-attachment-show', ['attachment' => $attachment, 'answer' => $answerId], false) }}" alt=""/>
                @endif
            </div>
        </div>
        @if($this->audioCloseWarning)
            <div class="absolute top-5 left-5">
                <div class="notification error">
                    <div class="title space-x-2 items-center"><x-icon.warning class="h-5"/><span>Let op</span></div>
                    <div class="body">
                        {{ __("attachment-modal.Als je de bijlage sluit is het geluidsfragment niet meer te beluisteren.") }} @if($this->timeout != null) {{ __("attachment-modal.Je hebt na het sluiten") }} {{ $this->timeout }} {{ __("attachment-modal.seconden om de vraag te beantwoorden") }} @endif
                    </div>
                </div>
            </div>
        @endif

    <script>
        dragElement(document.getElementById("attachment-{{ $attachment->uuid }}"));
    </script>
    </div>
@endif
@push('scripts')
    <script>
        document.addEventListener('livewire:load', function () {
            @this.on('clickPauseButtonAndCloseModal', event => {
                // var pauseButtons = document.getElementsByClassName('pause_button');
                var pauseButtons = document.querySelectorAll('.plyr__control--pressed');
                for (let item of pauseButtons) {
                    item.click();
                }
                @this.call('closeAttachmentModal');
            });
        });
    </script>

@endpush