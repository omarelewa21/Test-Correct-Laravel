@props([
    'content-sources',
    'menu',
])
@foreach($contentSources as $source)

    @if(class_exists($source))
    <x-menu.tab.item tab="{{$source::getName()}}"
                     :menu="$menu"
                     :highlight="$source::highlightTab()"
                     :selid="true"
                     >
        {{$source::getTranslation()}}
    </x-menu.tab.item>
    @endif
@endforeach
