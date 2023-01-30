<span @class([$labelTagWhite ? 'label-tag-white' : 'card-tag', 'grey' => !$published ])>
    @if($published)
        <x-icon.checkmark-small/> &nbsp; {{ __('test.published')}}
    @else
        <x-icon.edit/> &nbsp; {{ __('test.unpublished') }}
    @endif
</span>