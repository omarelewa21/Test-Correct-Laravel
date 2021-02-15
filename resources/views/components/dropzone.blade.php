@props([
    'title',
    'startGroup' => false,
    'type'
])
<div class="flex flex-col flex-1 space-y-3 leading-5">
    @isset($title)
        <h6 class="text-center"> {{ $title }}</h6>
    @endif

    <div {{ $attributes }}
         class="
        @if(!$startGroup) h-10 border-blue-grey bg-white w-full border-dashed border-2 rounded-10 @else startGroup @endif
        @if(isset($type) && $type == 'classify') p-2 @endif
            "
        @if($startGroup) style="min-height: 44px;" @elseif(isset($type) && $type == 'classify') style="min-height: 308px" @endif
    >{{ $slot }}</div>
{{--    <div class="border-primary bg-off-white w-full h-40 border-dashed border-2 rounded-10">ondragover</div>--}}
</div>