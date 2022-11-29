<div x-data="accordionBlock(@js($key), @js($emitWhenSet))"
     role="region"
        @class([
           'rounded-lg bg-white shadow',
           'accordion-disabled' => $disabled
           ])
>
    <div x-bind:id="id">
        <button
                x-on:click="expanded = !expanded"
                :aria-expanded="expanded"
                @disabled($disabled)
                class="flex w-full items-center rounded-lg px-10 py-3 text-xl font-bold group transition-shadow"
                x-on:mouseenter="if(!expanded) $el.classList.add('hover:shadow-hover')"
                x-on:mouseleave="$el.classList.remove('hover:shadow-hover')"
        >
            <div class="flex gap-4 items-center w-full">
                {{ $title }}
                {{ $titleLeft ?? '' }}
            </div>
            <div class="inline ml-auto">
                <span x-bind:class="{'rotate-svg-90': expanded}"
                      @class([
                        'flex items-center justify-center rounded-full min-w-[40px] w-10 h-10 transition',
                        'group-hover:bg-primary/5' => !$disabled
                        ])
                >
                <svg @class(['transition','group-hover:text-primary' => !$disabled])  width="9" height="13"
                     xmlns="http://www.w3.org/2000/svg">
                    <path class="stroke-current" stroke-width="3" d="M1.5 1.5l5 5-5 5" fill="none" fill-rule="evenodd"
                          stroke-linecap="round"/>
                </svg>
                    </span>
            </div>
        </button>
    </div>

    <div x-show="expanded"
         x-collapse
    >
        <div class="mx-10 pb-10 pt-4 border-t-3 border-sysbase flex max-w-full">
            {{ $body }}
        </div>
    </div>

    @if($upload)
        <input type="file" id="file-upload" multiple class="hidden" />

        <script>
            function fileUpload() {
                return {
                    isDropping: false,
                    isUploading: false,
                    progress: 0,
                }
            }
        </script>
    @endif
</div>