@if($attachment)
<div id="attachment" class="absolute top-28 left-20 z-30">
    <div class="flex-col">
        <div class="flex justify-end">
            <x-button.secondary id="attachmentdrag">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path></svg>
            </x-button.secondary>
            <x-button.cta wire:click="closeAttachmentModal" class="border-0">
                <x-icon.close/>
            </x-button.cta>
        </div>
        @if($attachment->type == 'video')
            <iframe class="bg-off-white" width="600" height="400" src="{{ $attachment->getVideoLink() }}"></iframe>
        @else
            <iframe class="bg-off-white" width="600" height="400" src="{{ route('student.question-attachment-show', $attachment->getKey()) }}"></iframe>
        @endif
    </div>
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