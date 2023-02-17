@props([
    'title',
    'startGroup' => false,
    'type'
])
<div class="pdf-200px pdf-ml-2 question-no-break-dropzone" id="main-{{$attributes->get('id')}}" >
    @isset($title)
        <h6 id="heading_for_{{ $attributes->get('id') }}" class="m-5 text-center pdf-dropzone-heading"> {{ $title }}</h6>
    @endif

    <div {{ $attributes }}
         class="pdf-dropzone
        @if(!$startGroup)  border-blue-grey bg-white w-full border-dashed border-2 @else startGroup @endif
        @if(isset($type) && $type == 'classify') p-1 rounded-2xl @else rounded-10 @endif
            "
        @if($startGroup) style="min-height: 44px;" @elseif(isset($type) && $type == 'classify') @endif
    >{{ $slot }}</div>
{{--    <div class="border-primary bg-off-white w-full h-40 border-dashed border-2 rounded-10">ondragover</div>--}}
</div>