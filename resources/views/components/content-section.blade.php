@props([
'title',
])
<div {{ $attributes->merge(['class' => 'flex flex-col py-5 px-7 bg-white rounded-10 content-section']) }}>
    <div class="px-2.5">
        <h2>{{ $title }}</h2>
    </div>
    <div class="divider mb-5 mt-2.5"></div>
    <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
        <div class="flex flex-1 flex-col ">
            {!! $slot !!}
        </div>
    </div>
</div>
