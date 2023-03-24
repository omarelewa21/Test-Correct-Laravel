<span @class([
        'inline-flex',
        'items-center',
        'gap-1',
        'card-tag',
        $labelColor ?? ($published ? 'green' : 'grey')
  ]) selid="publish-tag">
    @if($published)
        <x-icon.checkmark-small/><span class="leading-none">{{ __('test.published')}}</span>
    @else
        <x-icon.edit width="12" height="12"/><span class="leading-none">{{ __('test.unpublished') }}</span>
    @endif
</span>