@if($attachment)
    <div id="attachment"
         class="absolute -top-28 left-20 z-30 w-4/6 shadow-lg border border-blue-grey rounded-10 bg-black" style="height: 600px">
        <div class="flex-col relative h-full rounded-10">
            <div class="flex absolute top-0 right-0 justify-end space-x-2">
                <x-button.primary wire:click="closeAttachmentModal">
                    <x-icon.close class="text-white"/>
                </x-button.primary>
            </div>
            <div class="flex w-full h-full rounded-10">
                @if($attachment->type == 'video')
                    <iframe class="w-full h-full" src="{{ $attachment->getVideoLink() }}"></iframe>
                @elseif($attachment->file_mime_type == 'application/pdf')
                    <iframe class="w-full h-full"
                            src="{{ route('teacher.preview.question-pdf-attachment-show', ['attachment' => $attachment->getKey(), 'question' => $questionId], false) }}"></iframe>
                @elseif($attachment->file_mime_type == 'audio/mpeg')
                    <x-attachment.preview-audio :attachment="$attachment" :questionId="$questionId"/>
                @else
                    <iframe class="w-full h-full"
                            src="{{ route('teacher.preview.question-attachment-show', ['attachment' => $attachment->getKey(), 'question' => $questionId], false) }}"></iframe>
                @endif
            </div>
        </div>
        @if($this->audioCloseWarning)
            <div class="absolute top-5 left-5">
                <div class="notification error">
                    <div class="title space-x-2 items-center"><x-icon.warning class="h-5"/><span>Let op</span></div>
                    <div class="body">
                        Als je de bijlage sluit is het geluidsfragment niet meer te beluisteren. @if($this->timeout != null) Je hebt na het sluiten {{ $this->timeout }} seconden om de vraag te beantwoorden @endif
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif