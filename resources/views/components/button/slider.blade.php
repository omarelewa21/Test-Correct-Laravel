@props([
'label' => null,
'options',
'buttonWidth' => '105px',
'disabled' => false,
'disabledStyling' => false,
'useNamedSlots' => false,
'initialStatus' => null,
'toggleValue' => 0,
'identifier' => null,
])
<div wire:ignore
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
     data-toggle-value="@js($toggleValue)"
        @class([
           $attributes->get('class'),
           'slider-button-container',
           'disabled' => $disabled,
           ])
>
    @if($label)
        <label :for="$id('slider-button')">
            {{$label}}
        </label>
    @endif
    <div class="relative">
        <div :id="$id('slider-button')" @class(['flex', 'pointer-events-none' => $disabled])>
            @foreach($options as $id => $button)
                <div style="width: {{$buttonWidth}}"
                     @class([
                          'slider-option hover:text-primary group flex items-center justify-center h-10 bold border-t border-b first:border-l last:border-r first:rounded-l-lg last:rounded-r-lg',
                          'bg-off-white cursor-pointer border-bluegrey' => !$disabled,
                          'bg-white note hover:text-note border-lightGrey' => $disabled,
                        ])
                     @if(!$disabled) @click="clickButton($el)" @endif
                     data-active="false"
                >
                    <span data-id="{{$id}}"
                          class="inline-flex justify-center w-full px-3 border-r border-blue-grey group-last:border-r-0 pointer-events-none"
                    >
                        @if($useNamedSlots)
                            {{$$button}}
                        @else
                            {{ $button }}
                        @endif
                    </span>
                </div>
            @endforeach
        </div>
        <div :id="$id('slider-button')+'-handle'"
             style="width: @js($buttonWidth);"
             :style="{left: buttonPosition, width: buttonWidth}"
             class="border-2 rounded-lg border-primary absolute top-0 h-10 bottom-0 pointer-events-none slider-button-handle hidden"
        >
        </div>
    </div>
</div>