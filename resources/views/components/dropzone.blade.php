@props([
    'title' => false,
])
<div class="flex flex-col flex-1 items-center space-y-3 leading-5">
    @if($title)
        <h6> {{ $title }}</h6>
    @endif

    <div {{ $attributes }} class="border-blue-grey bg-white w-full h-40 border-dashed border-2 rounded-10">{{ $slot }}</div>
{{--    <div class="border-primary bg-off-white w-full h-40 border-dashed border-2 rounded-10">ondragover</div>--}}
</div>
