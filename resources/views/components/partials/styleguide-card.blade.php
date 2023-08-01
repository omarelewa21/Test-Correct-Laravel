@props([
    'title' => 'title',
    'itemsPerRow' => 6,
])
<x-accordion.container :active-container-key="'accordion-'.$title" >
    <x-accordion.block :key="'accordion-'.$title" @class([$attributes->get('accordionClass')])>
        <x-slot:title>{{$title}}</x-slot:title>
        <x-slot:body>
            <div class="flex w-full">

                <div @class([
                        'justify-between grid gap-4 items-center pt-2 w-full',
                        $attributes->get('class'),
                     ])
                     style="grid-template-columns: repeat({{$itemsPerRow}}, minmax(0,auto)); {{ $attributes->get('style') }}"
                >

                    {{ $slot }}

                </div>
            </div>
        </x-slot:body>
    </x-accordion.block>
</x-accordion.container>