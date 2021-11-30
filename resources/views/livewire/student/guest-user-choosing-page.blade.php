<div id="planned-body"
     x-data="{activeStudents: 0}"
     x-init="
        $el.parentElement.classList.add('flex','flex-1')"
     x-cloak
     class="w-full flex flex-col items-center student-bg pt-[70px]"
>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex flex-col mx-auto max-w-7xl space-y-4 transition-all duration-500">
                <div>
                    <span>{{ __('student.'.$status) }}</span>
                    <h1>{{ $testTake->test_name }}</h1>
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$testTake" :participatingClasses="$participatingClasses"/>
                </div>

                <div class="flex justify-center">
                    <div class="bg-white rounded-10 pt-5 p-8 w-full max-w-2xl">
                        <div class="px-3">
                            <h4 class="leading-8">{{ __('student.choose_your_student_guest_profile') }}</h4>
                        </div>
                        <div class="divider mt-4"></div>
                        <div class="flex flex-col">
                            @forelse($guestList as $key => $guest)
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
                            @empty
                                <div class="mt-4">{{ __('student.no_profiles_available') }}</div>
                            @endforelse

                            @error('participant_already_taken')
                                <div class="notification error stretched mt-4">
                                    <div class="flex items-center space-x-3">
                                        <x-icon.exclamation/>
                                        <span class="title">{{ $message }}</span>
                                    </div>
                                    <span class="body">{{ __('student.please_choose_a_different_participant') }}</span>
                                </div>
                            @enderror
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
