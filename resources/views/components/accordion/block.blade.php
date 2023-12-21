@props([
      'disabledBackGround' => false,
]
)
<div x-data="accordionBlock(@js($key), @js($emitWhenSet))"
     id="accordion-block"
     role="region"
     @if (!$disabledBackGround)
     @class([
        $attributes->get('class'),
        'accordion-block transition-shadow',
        'rounded-lg bg-white relative' => $mode === 'panel',
        'transparent' => $mode === 'transparent',
        'accordion-disabled' => $disabled
        ])
     @endif
     x-bind:class="{' bg-primary/5 border-dashed border-primary border-4 rounded-10 -m-1 ': droppingFile}"
     {{ $attributes->except(['class', 'key', 'emitWhenSet']) }}
     @if($mode === 'panel' && !$disabledBackGround)
        x-on:mouseenter="$el.classList.add('hover:shadow-hover')"
        x-on:mouseleave="$el.classList.remove('hover:shadow-hover')"
     @endif
     :data-block-id="id"
>
    @if($upload)
        <div x-data="fileUpload(@js($uploadModel), @js($uploadRules))"
             x-on:drop="if(expanded) handleDrop()"
             x-on:drop.prevent="if(expanded) handleFileDrop($event)"
             x-on:dragover.prevent=""
             x-on:dragenter.prevent="if(expanded) handleDragEnter()"
             x-on:dragleave.prevent="if(expanded) handleDragLeave()"
        >
            @endif
            <div x-bind:id="id">
                <button
                        x-on:click="expanded = !expanded; $dispatch('accordion-toggled')"
                        :aria-expanded="expanded"
                        @disabled($disabled)
                        @class([
                          'flex w-full items-center py-3 text-xl font-bold group transition-shadow',
                          'rounded-lg' => $mode === 'panel',
                          'border-b-3 border-sysbase' => $mode === 'transparent',
                          ($disabledBackGround) ? 'px-0' : 'px-10',
                          ])
                >
                    <div class="flex gap-4 items-center w-full flex-wrap">
                        {{ $title }}
                        {{ $titleLeft ?? '' }}
                    </div>
                    <x-button.collapse-chevron :disabled="$disabled"/>
                </button>
            </div>

            <div x-show="expanded"
                 x-collapse
                 block-body
            >
                <div @class(['accordion-content-slot | pt-4 flex max-w-full', 'border-t-3 border-sysbase pb-10' => $mode === 'panel', ($disabledBackGround) ? 'mx-0' : 'mx-10'] )>
                    {{ $body }}
                </div>
            </div>
            @if($coloredBorderClass)
                <div @class(['container-border-left', $coloredBorderClass])></div>
            @endif
            @if($upload)
        </div>
    @endif
</div>