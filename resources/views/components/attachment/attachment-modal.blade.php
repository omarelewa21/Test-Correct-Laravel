@if($attachment)
    <div id="attachment"
         class="fixed top-5 left-5 z-30 w-5/6 lg:w-4/6 h-[400px] lg:h-[500px] shadow-lg border border-blue-grey rounded-10 bg-black disable-swipe-navigation">
        <div class="flex-col relative h-full rounded-10">
            <div class="flex absolute top-0 right-0 justify-end space-x-2 z-10" style="-webkit-transform: translateZ(10px);">
                <x-button.secondary id="attachmentdrag" class="rotate-svg-45">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                    </svg>
                </x-button.secondary>
                <x-button.primary wire:click="closeAttachmentModal">
                    <x-icon.close class="text-white"/>
                </x-button.primary>
            </div>
            <div class="flex w-full h-full rounded-10 attachment-iframe-wrapper">
                @if($attachment->type == 'video')
                    <iframe class="w-full h-full" src="{{ $attachment->getVideoLink() }}"></iframe>
                @elseif($attachment->file_mime_type == 'application/pdf')
                    <iframe class="w-full h-full"
                            src="{{ route('student.question-pdf-attachment-show', ['attachment' => $attachment->getKey(), 'answer' => $answerId], false) }}"></iframe>
                @elseif($attachment->file_mime_type == 'audio/mpeg')
                    <x-attachment.audio :attachment="$attachment"/>
                @else
                    <iframe class="w-full h-full"
                            src="{{ route('student.question-attachment-show', ['attachment' => $attachment->getKey(), 'answer' => $answerId], false) }}"></iframe>
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

    <script>
        dragElement(document.getElementById("attachment"));

        function dragElement(elmnt) {
            var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
            if (document.getElementById(elmnt.id + "drag")) {
                // if present, the header is where you move the DIV from:
                document.getElementById(elmnt.id + "drag").onmousedown = dragMouseDown;
            } else {
                // otherwise, move the DIV from anywhere inside the DIV:
                elmnt.onmousedown = dragMouseDown;
            }

            function dragMouseDown(e) {
                e = e || window.event;
                e.preventDefault();
                // get the mouse cursor position at startup:
                pos3 = e.clientX;
                pos4 = e.clientY;
                document.onmouseup = closeDragElement;
                // call a function whenever the cursor moves:
                document.onmousemove = elementDrag;
            }

            function elementDrag(e) {
                e = e || window.event;
                e.preventDefault();
                // calculate the new cursor position:
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                // set the element's new position:
                elmnt.style.top = (elmnt.offsetTop - pos2) + "px";
                elmnt.style.left = (elmnt.offsetLeft - pos1) + "px";
            }

            function closeDragElement() {
                // stop moving when mouse button is released:
                document.onmouseup = null;
                document.onmousemove = null;
            }
        }
    </script>
@endif