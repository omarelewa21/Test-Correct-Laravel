@props([
    'maxWidth' => '500',
    'id'
])

<div x-data="{ open: false }"
     x-show.transition.duration.500ms="open" x-init="() => { setTimeout(() => { open = false }, 1); }"
     id="{{ $id }}"
     class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center px-4 md:px-0"
>
    <div class="flex flex-col content-section"
         @click.away="open = false"
         style="width: {{$maxWidth}}px;"
    >
        {{ $slot }}
    </div>
</div>