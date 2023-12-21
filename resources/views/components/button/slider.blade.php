@props([
'options',
'label' => null,
'buttonWidth' => '105px',
'disabled' => false,
'disabledStyling' => false,
'useNamedSlots' => false,
'initialStatus' => null,
'toggleValue' => null,
'identifier' => null,
'white' => false,
'allowClickingCurrentValue' => false,
])
<span wire:ignore
     {{ $attributes->except(['wire:model', 'class']) }}
     x-id="['slider-button']"
     x-data="sliderToggle(
             @if($attributes->wire('model')->value) @entangle($attributes->wire('model')) @else null @endif,
             @js($options),
             @js($initialStatus),
             @js($disabled),
             @js($identifier)
         )"
     x-on:slider-toggle-rerender="rerender()"
     x-on:scoring-elements-error.window="markInputElementsWithError()"
     x-intersect.once=" bootComponent()"
     data-toggle-value="@js($toggleValue)"
        @class([
           $attributes->get('class'),
           'slider-button-container block',
           'disabled' => $disabled,
           'white-variant' => $white,
           ])
>
    @if($label)
        <label :for="$id('slider-button')">
            {{$label}}
        </label>
    @endif
    <span class="relative block">
        <span :id="$id('slider-button')" @class(['flex', 'pointer-events-none' => $disabled])>
            @foreach($options as $id => $button)
                <span style="width: {{$buttonWidth}}; flex-shrink: 0;"
                     @class([
                          'slider-option | group flex items-center justify-center h-10 bold border-t border-b first:border-l last:border-r first:rounded-l-lg last:rounded-r-lg',
                          'bg-off-white cursor-pointer border-bluegrey' => !$disabled,
                          'bg-white note hover:text-note border-lightGrey' => $disabled,
                        ])
                     @if(!$disabled) @click="clickButton($el, '{{$allowClickingCurrentValue}}')" @endif
                     data-active="false"
                >
                    <span data-id="{{ $id }}"
                          class="inline-flex justify-center w-full px-3 border-r border-blue-grey group-last:border-r-0 pointer-events-none"
                    >
                        @if($useNamedSlots)
                            {{$$button}}
                        @else
                            {{ $button }}
                        @endif
                    </span>
                </span>
            @endforeach
        </span>
        <span :id="$id('slider-button')+'-handle'"
             style="width: @js($buttonWidth);"
             :style="{left: buttonPosition, width: buttonWidth}"
             class="slider-button-handle | absolute top-0 h-10 bottom-0 pointer-events-none  hidden"
        >
        </span>
    </span>
</span>