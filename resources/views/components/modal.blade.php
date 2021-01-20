@props([
    'maxWidth' => '500',
    'id'
])

<div x-data="{ open: false }"
     x-show.transition.duration.500ms="open" x-init=""
     id="{{ $id }}"
     class="fixed inset-0 bg-white bg-opacity-75 flex items-center justify-center px-4 md:px-0"
>
    <div class="flex flex-col content-section"
         @click.away="open = false"
         style="width: {{$maxWidth}}px;"
    >
        <div class="px-10 pt-10">
            {{ $title }}
        </div>

        <div class="divider mx-8 my-3"></div>

        <div class="px-10 body1">
            {{ $body }}
        </div>

        <div class="py-5 px-10 flex justify-end space-x-6">
            {{ $footer }}
        </div>
    </div>
</div>