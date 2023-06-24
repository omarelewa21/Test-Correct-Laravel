<div>
    @foreach($icons as $icon)
        @if($icon['show'])
            <span @if (!$loop->last) class="mr-1" @endif>
                @include($icon['path'], $icon['props'])
            </span>
        @endif
    @endforeach
</div>