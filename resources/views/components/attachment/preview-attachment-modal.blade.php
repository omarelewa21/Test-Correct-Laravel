@if($attachment)
    <div id="attachment"
         class="fixed top-5 left-5 z-30 shadow-lg border border-blue-grey rounded-10 bg-black {{ $this->getAttachmentModalSize() }}"
    >
        <div class="hidden h-[45vw] w-3/4 h-1/2 w-5/6 lg:w-4/6 h-[80vh] w-[80vw] h-[45vw]"></div>
        <div class="flex-col relative h-full rounded-10">
            <div class="flex absolute top-0 right-0 justify-end space-x-2">
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
                            src="{{ route('teacher.preview.question-pdf-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionId], false) }}"></iframe>
                @elseif($this->attachmentType == 'audio')
                    <x-attachment.preview-audio :attachment="$attachment" :questionId="$questionId"/>
                @else
                    <img class="w-full h-full"
                            src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $questionId], false) }}" alt=""/>
                @endif
            </div>
        </div>
        @if($this->audioCloseWarning)
            <div class="absolute top-5 left-5">
                <div class="notification error">
                    <div class="title space-x-2 items-center"><x-icon.warning class="h-5"/><span>{{__("attachment-modal.Let op")}}</span></div>
                    <div class="body">
                        {{__("attachment-modal.Als je de bijlage sluit is het geluidsfragment niet meer te beluisteren.")}} @if($this->timeout != null) {{__("attachment-modal.Je hebt na het sluiten")}} {{ $this->timeout }} {{__("attachment-modal.seconden om de vraag te beantwoorden")}} @endif
                    </div>
                </div>
            </div>
        @endif
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
                setTimeout(() => {
                    @this.call('closeAttachmentModal');
                }, 500);
            });
        });
    </script>

@endpush