@props([
    'title',
    'startGroup' => false,
    'type'
])
<div class="flex flex-col flex-1 min-w-[16rem] justify-content-space-evenly space-y-3 leading-5 pdf-200px pdf-ml-2 question-no-break-dropzone"
     id="main-{{$attributes->get('id')}}">
    @isset($title)
        <h6 id="heading_for_{{ $attributes->get('id') }}" class="text-center pdf-dropzone-heading"> {{ $title }}</h6>
    @endif

    <div {{ $attributes->except('class') }}
         @class([
           "pdf-dropzone",
           "startGroup" => $startGroup,
           "border-blue-grey bg-white w-full border-dashed border-2 " => !$startGroup,
           "p-1 rounded-2xl" => isset($type) && $type == 'classify',
           "rounded-10" => !isset($type) || $type != 'classify'
         ])
         @if($startGroup) style="min-height: 44px;" @endif
    >{{ $slot }}</div>
{{--    <div class="border-primary bg-off-white w-full h-40 border-dashed border-2 rounded-10">ondragover</div>--}}
</div>