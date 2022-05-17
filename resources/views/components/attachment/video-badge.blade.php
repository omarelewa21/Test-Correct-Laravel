<div class="flex border rounded-lg border-blue-grey items-center mr-4 mb-2"
     x-data="badge('{{ $video['link'] }}')"
     @attachments-updated.window="setIndex()"
     wire:key="{{ $attributes['wire:key'] }}"
>
    <div class="flex p-2 border-r border-blue-grey h-full items-center"
         wire:key="icon-{{ $attributes['wire:key'] }}"
    >
        @if($host === 'vimeo')
            <x-icon.vimeo/>
        @else
            <x-icon.youtube/>
        @endif
    </div>
    <div class="flex base items-center relative"
         wire:key="title-{{ $attributes['wire:key'] }}"
    >
        <div class="flex base items-center relative">
            <span class="pl-2" x-text="index + ':'"></span>
            <span class="p-2 text-base max-w-[200px] truncate"
                  :class="{'text-midgrey': resolvingTitle}"
                  :title="videoTitle"
                  x-text="videoTitle"
            >
            </span>
            <button class="py-3 px-4 flex items-center h-full rounded-md hover:bg-primary hover:text-white transition"
                    @click="options = true"
            >
                <x-icon.options/>
            </button>

            <div x-cloak
                 x-show="options"
                 x-ref="optionscontainer"
                 class="absolute right-0 top-10 bg-white py-2 main-shadow rounded-10 w-72 z-10"
                 @click.outside="options=false"
                 x-transition:enter="transition ease-out origin-top-right duration-200"
                 x-transition:enter-start="opacity-0 transform scale-90"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition origin-top-right ease-in duration-100"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-90"
            >

                <button class="flex items-center space-x-2 py-1 px-4 base hover:text-primary hover:bg-offwhite transition w-full"
                        @click="$dispatch('delete-modal', ['video', '{{ $video['id'] }}'])"
                >
                    <x-icon.remove/>
                    <span class="text-base bold inherit">{{ __('cms.Verwijderen') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
