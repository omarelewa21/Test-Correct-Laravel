<div>
    @foreach($icons as $icon)
        @if($icon['show'])
            @include($icon['path'])
        @endif
    @endforeach
</div>