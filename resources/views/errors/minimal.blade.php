<x-layouts.base>
    <div class="flex items-center justify-center h-screen">
        {{-- top content block height:120px --}}
        <div class="bg-white rounded-t-[10px] px-10 pt-[31px] w-[480px] pb-0 shadow-lg flex flex-col relative ">
            <div class="absolute top-[-100px] left-1/2 -translate-x-1/2 flex">
                <div class="w-[368px] w-[342px]">
                    <x-animations.error-pages/>
                </div>
            </div>


            {{-- middle content block height:377px --}}
            <div class="bg-white px-2 py-[30px] flex flex-col"
                 style="min-height: 377px">


                <div class="flex flex-col mt-auto pt-4 justify-center">
                    <div >
                        <h3 class="text-center text-[64px] leading-none">@yield('code')</h3>

                        <h4 class="text-center text-[24px] leading-none">@yield('message')</h4>

                    </div>
                    <div class="mt-4">
                        <x-button.cta
                                selid="login-btn"de
                                class="w-full justify-center"
                                size="md"
                                type="link"
                                href="{!! route('redirect-to-dashboard') !!}"
                        >
                            <x-icon.arrow-left></x-icon.arrow-left><span>{{ __('app.Ga naar dashboard') }}</span>
                        </x-button.cta>
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-layouts.base>
</body>
</html>
