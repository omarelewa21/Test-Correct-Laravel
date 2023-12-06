<span x-data="initReadSpeakerLanguage()" style="position: relative; top:-16px; isolation: isolate">
      @foreach(['de', 'en', 'fr', 'nl', 'es'] as $language)
        <button
            x-data="{ isHovering: false }"
            x-on:mouseenter="isHovering = true"
            x-on:mouseleave="isHovering = false"
            x-show="isCurrent('{{ $language }}')"
            type="button"
            style="margin-top:-17px"
            class="relative bg-white border-1 border-primary  px-2 py-1 rounded-full h-[40px] w-[40px] z-10"
            x-on:click="openPopover()"
            :class="{ 'bg-blue-50': isHovering }"
            title="{{__('test_take.language.'.$language)}}"
        >
            <div

                class="w-8 h-8 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
                style="border-color: rgba(0, 77, 245, 0.5); border-width: 1px; border-style: solid; border-radius: 50%;"

            ></div>

                <x-dynamic-component :component="'icon.flag.'.$language"/>

        </button>
    @endforeach
        <div
            x-show="isOpen"
            x-on:click.away="closePopover()"
            class="absolute z-[5] w-[40px] bg-white rounded-t-full rounded-b-full shadow-lg"
            style="top: -170px; left: 0px; padding-bottom: 40px"
        >


        @foreach(['de', 'en', 'fr', 'nl', 'es'] as $language)
                <button
                    x-data="{isHovering: false}"
                    x-on:mouseenter="isHovering = true"
                    x-on:mouseleave="isHovering = false"
                    type="button"
                    x-show="!isCurrent('{{ $language }}')"
                    x-on:click="selectLanguage('{{$language}}')"
                    class="px-2 py-1 rounded-full h-[40px] w-[40px] bg-white relative"
                    title="{{__('test_take.language.'.$language)}}"

                >
                     <div
                         x-show="isHovering"
                         class="w-8 h-8 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2"
                         style="border-color: rgba(0, 77, 245, 0.5); border-width: 1px; border-style: solid; border-radius: 50%; ;"

                     ></div>


                            <x-dynamic-component :component="'icon.flag.'.$language"/>

    </button>
            @endforeach

    </div>

</span>
