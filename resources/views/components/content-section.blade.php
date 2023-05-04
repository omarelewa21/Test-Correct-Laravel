@props([
'title',
'withUploadHandling' => false
])
<div {{ $attributes->merge(['class' => 'flex flex-col py-5 px-7 bg-white rounded-10 content-section']) }}
     @if($withUploadHandling)
     @dragover.prevent="if ($event.target.closest('.ck-editor__main') === null) { $el.classList.add('dragover') }"
     @dragleave.prevent="$el.classList.remove('dragover')"
     @drop.prevent="$el.classList.remove('dragover');  $dispatch('newfile', $event)"
     droppable
     @video-url-not-supported.window="Notify.notify($event.detail, 'error')"
     @endif
>
    <div class="px-2.5">
        <h2 selid="header">{{ $title }}</h2>
    </div>
    <div class="divider mb-5 mt-2.5"></div>
    <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
        <div class="flex flex-1 flex-col ">
            {!! $slot !!}
        </div>
    </div>
</div>
