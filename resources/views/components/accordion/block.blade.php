<div x-data="accordionBlock( @js($key) )"
     role="region"
     class="rounded-lg bg-white shadow">
    <div x-bind:id="id">
        <button
                x-on:click="expanded = !expanded"
                :aria-expanded="expanded"
                class="flex w-full items-center  px-10 pt-4 pb-3 text-xl font-bold"
        >
            <div class="flex gap-4 items-center w-full">
                <span class="rounded-full text-sm flex items-center justify-center border-3 relative px-1.5 border-sysbase transition-colors"
                      x-bind:class="expanded ? 'text-white bg-sysbase' : 'bg-transparent'"
                      style="min-width: 30px; height: 30px"
                >
                    <span class="mt-px question-number bold ">{{ $key }}</span>
                </span>
                <span class="bold text-xl">{{ $title }}</span>
                {{ $titleLeft ?? '' }}
            </div>
            <div class="inline ml-auto" x-bind:class="{'rotate-svg-90': expanded}">
                <x-icon.chevron class="transition-[rotate]"/>
            </div>
        </button>
    </div>

    <div x-show="expanded"
         x-collapse
    >
        <div class="mx-10 pb-6 pt-4 border-t-3 border-sysbase flex max-w-full">
            {{ $body }}
        </div>
    </div>
</div>