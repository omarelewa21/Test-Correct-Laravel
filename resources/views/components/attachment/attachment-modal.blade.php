@if($attachment)
    <div id="attachment-{{$attachment->uuid}}"
        @class([
            'fixed z-30 shadow-lg border border-blue-grey rounded-10 bg-sysbase/30 disable-swipe-navigation top-10 left-10 max-width-90',
            $this->getAttachmentModalSize()
        ])
        x-data="attachmentModal(@js($this->attachmentType))"
        wire:ignore.self
    >
        <div @class(['box-border w-full h-full', 'resizers' => $this->attachmentType != 'audio'])>
            <div class='resizer top-left'></div>
            <div class='resizer top-right'></div>
            <div class='resizer bottom-left'></div>
            <div class='resizer bottom-right'></div>

            <div @class([
                  'flex-col relative w-full h-full rounded-10 overflow-auto',
                  'image-max-height max-h-[80vh]' => $this->attachmentType === 'image'
                  ])>
                <div class="flex absolute top-0 right-0 justify-end space-x-2 z-10" style="-webkit-transform: translateZ(10px);">
                    <x-button.secondary id="attachment-{{$attachment->uuid}}drag" class="rotate-svg-45" selid="drag-attachment-btn">
                        <x-icon.move class="w-6 h-6"/>
                    </x-button.secondary>
                    <x-button.primary wire:click="closeAttachmentModal" selid="close-attachment-btn">
                        <x-icon.close class="text-white"/>
                    </x-button.primary>
                </div>
                <div class="flex w-full h-full rounded-10 attachment-iframe-wrapper">
                    @hasSection('typeImplementations')
                        @yield('typeImplementations')
                    @else
                        @if($this->attachmentType == 'video')
                            <iframe class="w-full h-full" src="{{ $attachment->getVideoLink() }}" selid="youtube-attachment"></iframe>
                        @elseif($this->attachmentType == 'pdf')
                            <iframe class="w-full h-full" selid="document-attachment"
                                    src="{{ route('student.answer-pdf-attachment-show', ['attachment' => $attachment, 'answer' => $answerId], false) }}"></iframe>
                        @elseif($this->attachmentType == 'audio')
                            <x-attachment.audio :attachment="$attachment" />
                        @else
                            <img src="{{ route('student.answer-attachment-show', ['attachment' => $attachment, 'answer' => $answerId], false) }}"
                                 selid="image-attachment"
                                 alt="@lang('cms.bijlage')"
                                 style="object-fit: contain; transition: opacity 150ms ease"
                                 x-on:load="imageLoaded()"
                                 :style="{'width': imageWidth, 'height': imageHeight}"
                            />
                        @endif
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
        </div>
    </div>
@endif