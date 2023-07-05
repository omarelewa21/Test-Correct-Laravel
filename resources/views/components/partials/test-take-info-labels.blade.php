<div>
    @foreach($icons as $icon)
        @if($icon['show'])
            <span @if (!$loop->last) class="mr-1" @endif>
                <x-dynamic-component component="icon.{{ $icon['name'] }}" title="{{$icon['tooltip']}}" />
            </span>
        @endif
    @endforeach
</div>