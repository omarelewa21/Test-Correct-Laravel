<x-modal id="{{$this->modalId}}"  maxWidth="3xl" :showCancelButton="false" wire:model="showModal">
    <x-slot name="title">
        <div class="flex justify-between">
            <span>{{__("Hoe wil je een toets creëren?")}}</span>
            <span wire:click="showModal()" class="cursor-pointer">x</span>
        </div>
    </x-slot>
    <x-slot name="body">
        <div class="flex px-1">
            <div name="block-container" class="grid grid-cols-2 pt-5">

                <div class="flex flex-column justify-center flex-wrap border-[3px] rounded-lg border-blue-grey mr-2 px-6 pb-8" name="block-1">
                    <div class="mt-[-1rem] h-[78px] w-full flex justify-center mb-4 -mt-4" name="svg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="65" height="78" viewBox="0 0 65 78">
                            <g fill="none" fill-rule="evenodd">
                                <g>
                                    <g>
                                        <g>
                                            <g>
                                                <g>
                                                    <path fill="#FFF" stroke="#041F74" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M1 75h59c.552 0 1-.448 1-1V15.414c0-.265-.105-.52-.293-.707L46.293.293C46.105.105 45.85 0 45.586 0H1C.448 0 0 .448 0 1v73c0 .552.448 1 1 1z" transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5)"></path>
                                                    <g>
                                                        <g transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(11.5 10.5)">
                                                            <path stroke="#CEDAF3" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8.5 2.5L27.5 2.5"></path>
                                                            <circle cx="2.5" cy="2.5" r="2.5" fill="#CEDAF3"></circle>
                                                        </g>
                                                        <g stroke="#CEDAF3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                                            <path d="M0 .5L36 .5M0 5.5L28 5.5" transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(11.5 10.5) translate(1 8.5)"></path>
                                                        </g>
                                                        <g fill="#CEDAF3" stroke="#CEDAF3" stroke-linejoin="round" stroke-width="2">
                                                            <path d="M0 0H4V4H0zM16 0H20V4H16zM24 0H28V4H24zM8 0H12V4H8z" transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(11.5 10.5) translate(1 19)"></path>
                                                        </g>
                                                        <g transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(11.5 10.5) translate(0 28)">
                                                            <path stroke="#CEDAF3" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M8.5 2.5L36.5 2.5"></path>
                                                            <circle cx="2.5" cy="2.5" r="2.5" fill="#CEDAF3"></circle>
                                                        </g>
                                                        <g stroke="#CEDAF3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                                                            <path d="M0 .5L36 .5M0 5.5L28 5.5" transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(11.5 10.5) translate(1 36.5)"></path>
                                                        </g>
                                                    </g>
                                                    <g transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) translate(15.5 38.5)">
                                                        <circle cx="15.5" cy="15.5" r="15.5" fill="#004DF5"></circle>
                                                        <g stroke="#FFF" stroke-linecap="round" stroke-width="3">
                                                            <path d="M1.5 7.5L11.5 7.5" transform="translate(9 8.5)"></path>
                                                            <path d="M6.5 12.5L6.5 2.5" transform="translate(9 8.5) matrix(-1 0 0 1 13 0)"></path>
                                                        </g>
                                                    </g>
                                                    <path fill="#FFF" stroke="#041F74" stroke-linejoin="round" stroke-width="3" d="M61 0L46 0 46 15z" transform="translate(-535 -596) translate(120 596) translate(304) translate(111.714) translate(1.5 1.5) matrix(1 0 0 -1 0 15)"></path>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>

                    </div>
                    <h4 class="w-full text-center leading-8">{{__("Toets Construeren")}}</h4>
                    <div class="w-full text-center">{{__("Ga zelf aan de slag met het maken van een toets")}}</div>
                    <small class="w-full text-center mb-4 mt-3">{{__("Stel jouw toets in en zet jouw toets op met vraaggroepen en
                        vragen")}}</small>
                    <div class="w-full text-center" >
                        <x-button.cta wire:click="goToCreateTest">{{__("Toets Construeren")}}</x-button.cta>
                    </div>
                </div>

                <div class="flex flex-column justify-center flex-wrap border-[3px] rounded-lg border-blue-grey ml-2 px-6 pb-8" name="block-2">
                    <div class="mt-[-1rem] h-[78px] w-full flex justify-center mb-4 -mt-4" name="svg">
                        <svg xmlns="http://www.w3.org/2000/svg" width="65" height="78" viewBox="0 0 65 78">
                            <g fill="none" fill-rule="evenodd">
                                <g>
                                    <g>
                                        <g>
                                            <g>
                                                <g>
                                                    <path fill="#FFF" stroke="#041F74" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M1 75h59c.552 0 1-.448 1-1V15.414c0-.265-.105-.52-.293-.707L46.293.293C46.105.105 45.85 0 45.586 0H1C.448 0 0 .448 0 1v73c0 .552.448 1 1 1z" transform="translate(-839 -596) translate(120 596) translate(608) translate(111.714) translate(1.5 1.5)"></path>
                                                    <g>
                                                        <path stroke="#CEDAF3" stroke-width="3" d="M24 0c5.608 0 10.447 3.298 12.681 8.06C42.477 8.651 47 13.547 47 19.5 47 25.851 41.851 31 35.5 31l-.165-.001-.04.001h0H9.067l-.016-.002L9 31C4.118 31 .144 27.113.004 22.265L0 22c0-4.97 4.03-9 9-9 .349 0 .693.02 1.032.058C10.516 5.765 16.585 0 24 0z" transform="translate(-839 -596) translate(120 596) translate(608) translate(111.714) translate(1.5 1.5) translate(7 14)"></path>
                                                        <g transform="translate(-839 -596) translate(120 596) translate(608) translate(111.714) translate(1.5 1.5) translate(7 14) translate(15.314 5.518)">
                                                            <path fill="#CEDAF3" d="M2.169 13.67c1.59-1.272 4.71-1.01 6.437.44 1.47 1.234 3.882 1.438 5.436.682-.005-.037-2.095 1.978-5.816 1.922-3.72-.057-5.614-2.481-6.057-3.044z"></path>
                                                            <path fill="#CEDAF3" d="M3.61 13.268c0-1.342-.059-2.325.18-3.398.357-1.61 1.073-2.414.804-2.772-.268-.358-.894-.358-1.073-.179-.179.179-.268-.179 0-.447.269-.268.894-.805 1.789-.894.894-.09.894.09 1.61-.447.714-.537 2.86-1.52 5.096-.537 2.235.984 2.056 1.7 2.325 1.878.268.179.626.268.536.537-.076.227-.507.178-.983 0 .536.298.804.536.804.715 0 .268-.268.179-.894 0s-2.235-.268-3.219.358c-.983.625-.447.536-.805 1.162-.357.626-1.073.984-1.52.984-.447 0-.493-.133-.09-.537.27-.268.448-.477.27-.894-1.52.715-2.952 2.235-3.22 4.381-.163 1.302-1.61 1.408-1.61.09z"></path>
                                                            <circle cx="8.618" cy="8.618" r="7.618" stroke="#CEDAF3" stroke-width="2"></circle>
                                                        </g>
                                                    </g>
                                                    <g transform="translate(-839 -596) translate(120 596) translate(608) translate(111.714) translate(1.5 1.5) translate(15.5 38.5)">
                                                        <circle cx="15.5" cy="15.5" r="15.5" fill="#004DF5"></circle>
                                                        <g stroke="#FFF" stroke-linecap="round" stroke-width="3">
                                                            <g>
                                                                <path d="M-0.5 4.5L8.5 4.5" transform="matrix(1 0 0 -1 9.5 22.5) translate(2) rotate(90 4 4.5)"></path>
                                                                <path d="M2 3L6 7 2 11" transform="matrix(1 0 0 -1 9.5 22.5) translate(2) rotate(90 4 7)"></path>
                                                            </g>
                                                            <path d="M0 13L12 13" transform="matrix(1 0 0 -1 9.5 22.5)"></path>
                                                        </g>
                                                    </g>
                                                    <path fill="#FFF" stroke="#041F74" stroke-linejoin="round" stroke-width="3" d="M61 0L46 0 46 15z" transform="translate(-839 -596) translate(120 596) translate(608) translate(111.714) translate(1.5 1.5) matrix(1 0 0 -1 0 15)"></path>
                                                </g>
                                            </g>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </svg>

                    </div>
                    <h4 class="w-full text-center leading-8">{{__("Toets uploaden")}}</h4>
                    <subtitle class="w-full text-center">{{__("Laat een bestaande toets digitaliseren")}}</subtitle>
                    <small class="w-full text-center mb-4 mt-3">{{__("Gelieve aan te leveren als:")}} <br> {{__("PDF, Word, Wintoets")}}</small>
                    <div class="w-full text-center"

                    >
                        <x-button.cta wire:click="goToUploadTest()">{{__("Toets uploaden")}}</x-button.cta>
                    </div>
                </div>

            </div>
        </div>
        <div class="absolute bottom-8 inset-x-0 h-4 flex items-center justify-center space-x-1">
            <div class="border-0 rounded-xl bg-primary h-[14px] w-[14px]"></div>
            <div class="border-0 rounded-xl bg-bluegrey h-[14px] w-[14px]"></div>
        </div>
    </x-slot>
    <x-slot name="actionButton">
        <div class="w-[44vw] flex justify-center items-center">
            <div class="mt-8"></div> {{-- 44vw depends on maxWidth 2xl... --}}
        </div>
    </x-slot>
</x-modal>

