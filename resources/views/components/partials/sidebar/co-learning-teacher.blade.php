<div class="drawer flex z-[20]"
     selid="co-learning-teacher-drawer"
     x-init="
        collapse = window.innerWidth < 1000;
     "
     x-data="{collapse: false}"
>
    <div class="flex flex-col w-full justify-between">
        <div class="flex flex-col">
            <div class="flex justify-between px-6 mt-[10px] pt-[10px] pb-2 border-b border-bluegrey">
                <div><span class="bold">aanwezig 24</span>/30</div>
                <div><span class="bold">vraag 1</span>/5</div>
            </div>
            <div class="flex flex-col bg-white w-[var(--sidebar-width)] divide-y divide-bluegrey justify-center overflow-auto" >
                @for($i=0;$i<24;$i++)
                    {{-- student row --}}
                    <div class="flex mx-6 items-center h-10 justify-between">
                        {{-- left --}}
                        <div class="flex items-center h-full space-x-1">
                            <span class="min-w-[1rem] w-4 flex items-center justify-center">
                                @if($i === 0)
                                    <x-icon.time-dispensation class="text-orange"/>
                                @elseif($i === 1)
                                    <x-icon.warning class="text-red-500"/>
                                @else
                                    <x-icon.checkmark-small class="text-cta"/>
                                @endif
                            </span>
                            <span class="min-w-[1rem] w-4 flex items-center justify-center">
{{--                                <x-icon.close-small/>--}}
                                @if($i === 0)
                                    <x-icon.smiley-normal class="text-midgrey"/>
                                @elseif($i === 1)
                                    <x-icon.smiley-normal class="text-orange"/>
                                @elseif($i === 2)
                                    <x-icon.smiley-sad class="text-red-500"/>
                                @else
                                    <x-icon.smiley-happy class="text-cta"/>
                                @endif

                            </span>
                            <span class="flex w-full">Froukje Lindemans</span>
                        </div>
                        {{-- right --}}
                        <div >
                            <x-icon.upload/>
                        </div>
                    </div>
                @endfor
            </div>
        </div>

        <div class="fixed bottom-0 w-[var(--sidebar-width)] flex justify-between items-center px-6 pt-[15px] pb-[15px] bg-white h-[70px] footer-shadow">
            <x-button.text-button>
                <x-icon.arrow-left/>
                <span class="ml-2">{{ __('co-learning.previous') }}</span>
            </x-button.text-button>
            <x-button.primary class="px-4 flex-0">
                <span class="mr-2">{{ __('co-learning.next') }}</span>
                <x-icon.arrow/>
            </x-button.primary>
        </div>
    </div>
    <!-- Well begun is half done. - Aristotle -->
</div>