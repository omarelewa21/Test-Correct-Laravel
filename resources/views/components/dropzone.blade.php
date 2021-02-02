@props([
    'title',
    'startGroup' => false,
    'type'
])
<div class="flex flex-col flex-1 items-center space-y-3 leading-5">
    @isset($title)
        <h6> {{ $title }}</h6>
    @endif

    <div {{ $attributes }} class="@if(!$startGroup)p-4 border-blue-grey bg-white w-full border-dashed border-2 rounded-10 @endif">{{ $slot }}</div>
{{--    <div class="border-primary bg-off-white w-full h-40 border-dashed border-2 rounded-10">ondragover</div>--}}
</div>
