<div id="planned-body"
     x-data="{}"
     x-init="addRelativePaddingToBody('planned-body'); $el.parentElement.classList.add('flex','flex-1')"
     x-cloak
     class="w-full flex flex-col items-center student-bg"
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('planned-body')"
>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex flex-col mx-auto max-w-7xl space-y-4 transition-all duration-500">
                <div>
                    <h1>{{ $testTake->test_name }}</h1>
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$testTake"/>
                </div>

                <div class="flex justify-center">
                    <div class="bg-white rounded-10 pt-5 p-8 w-full max-w-2xl">
                        <div class="px-3">
                            <h4 class="leading-8">Kies jouw studenten gastprofiel</h4>
                        </div>
                        <div class="divider mt-4"></div>
                        <div class="flex flex-col">
                            @foreach($guestList as $key => $guest)
                                <div class="relative w-full flex hover:font-bold p-5 rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between items-center hover:-mb-px cursor-pointer"
                                     wire:key="{{ $key }}"
                                     wire:click="continueAs('{{ $guest['uuid'] }}')"
                                >
                                    <span>{{ $guest['name'] }}</span>
                                    <x-icon.arrow class="inline-flex"/>
                                </div>
                                <div class="h-px bg-blue-grey mx-2"></div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row justify-center items-center md:space-x-4">
                    <x-button.primary type="link" href="">
                        <x-icon.download/>
                        <span>{{__('auth.download_student_app')}}</span>
                    </x-button.primary>
                    <h5 class="hidden inline-flex mt-2 md:mt-0">&amp;</h5>
                    <x-button.text-button class="hidden">
                        <span>{{__('auth.request_account_from_teacher')}}</span>
                        <x-icon.arrow/>
                    </x-button.text-button>
                </div>
            </div>
        </div>
    </div>
</div>
