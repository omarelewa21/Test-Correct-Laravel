@props([
'label' => null,
'options',
'buttonWidth' => '105px',
'disabled' => false,
'disabledStyling' => false,
'useNamedSlots' => false,
'initialStatus' => null,
'toggleValue' => 0,
])
<div wire:ignore
     {{ $attributes->merge(['class' => 'slider-button-container'])->except('wire:model') }}
     x-id="['slider-button']"
     x-data="sliderToggle(
             @if($attributes->wire('model')->value) @entangle($attributes->wire('model')) @else null @endif,
             @js($options),
             @js($initialStatus)
         )"
     x-on:slider-toggle-rerender="rerender()"
     data-toggle-value="@js($toggleValue)"
>
    @if($label)
        <label :for="$id('slider-button')">
            {{$label}}
        </label>
    @endif
    <div class="relative">
        <div :id="$id('slider-button')" class="flex">
            @foreach($options as $id => $button)
                <div style="width: {{$buttonWidth}}"
                     class="slider-option hover:text-primary group flex items-center justify-center h-10 bold border-blue-grey border-t border-b first:border-l last:border-r first:rounded-l-lg last:rounded-r-lg
                     {{ $disabledStyling ? 'bg-white opacity-70 note hover:text-note' : 'bg-off-white cursor-pointer'}}
                     "
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
             class="border-2 rounded-lg border-primary absolute h-10 bottom-0 pointer-events-none slider-button-handle hidden"
        >
        </div>
    </div>
</div>