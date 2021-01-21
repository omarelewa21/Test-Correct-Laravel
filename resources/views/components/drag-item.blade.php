@props([
    'state' => 'idle',
    'wFull' => false
])
<?php
    $idle =  'bg-system-secondary base border-system-secondary';
    $onDrag = 'bg-primary-light primary border-primary-light';
    $onDragEnter = 'bg-system-secondary primary border-primary';
    $onDrop = 'bg-primary text-white border-primary';

    if($state == 'idle') { $activeState = $idle;}
    if($state == 'onDrag') { $activeState = $onDrag;}
    if($state == 'onDragEnter'){ $activeState = $onDragEnter;}
    if($state == 'onDrop'){ $activeState = $onDrop;}

?>


<div id="drag-item"
     class="{{$activeState}} inline-flex mb-3 mr-2 rounded-10 border-2 bold font-size-18 flex justify-between items-center px-4 p-1 select-none cursor-pointer @if($wFull) w-full @else max-w-max @endif"
     draggable="true"
>
    <span class="mr-3">{{ $slot }}</span>

    <x-icon.grab/>
</div>