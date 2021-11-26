<div id="planned-body"
     x-data="{sortField: @entangle('sortField'), sortDirection: @entangle('sortDirection'), activeStudents: 0}"
     x-init="
        $el.parentElement.classList.add('flex','flex-1')"
     x-cloak
     class="w-full flex flex-col items-center student-bg pt-[70px]"
>
    <div class="flex flex-col w-full mt-10">
        <div class="w-full px-4 lg:px-8 xl:px-12 transition-all duration-500">
            <div class="flex flex-col mx-auto max-w-7xl space-y-4 transition-all duration-500">
                <div>
                    <h1>{{ $testTake->test_name }}</h1>
                </div>
                <div>
                    <x-partials.waiting-room-grid :waitingTestTake="$testTake" :participatingClasses="$participatingClasses"/>
                </div>

                <div class="flex justify-center">
                    <div class="bg-white rounded-10 pt-5 p-8 w-full max-w-2xl">
                        <div class="px-3">
                            <h4 class="leading-8">{{ __('student.grades_for_guest_accounts') }}</h4>
                            @if($this->canReviewTestTake())
                                <h7 class="">{{ __('student.click_name_to_review_test') }}</h7>
                            @endif
                        </div>
                        <div class="mt-3 px-3 flex w-full justify-between base">
                            <button
                                    class="button text-button space-x-2.5 focus:outline-none transition text-base"
{{--                                    class="{{ ($this->sortField == 'users.name' && $this->sortDirection == 'asc') ? 'rotate-svg-270' : 'rotate-svg-90' }} text-base"--}}
                                    :class="(sortField == 'users.name' && sortDirection == 'asc') ? 'rotate-svg-270' : 'rotate-svg-90' "
                                    wire:click="sortGuestNames"
                                    wire:loading.class="underline"
                            >
                                <span>{{ __('general.name') }}</span>
                                <x-icon.chevron-small opacity="1"/>
                            </button>
                            <x-button.text-button
                                    class="{{ ($this->sortField == 'test_participants.rating' && $this->sortDirection == 'asc') ? 'rotate-svg-270' : 'rotate-svg-90' }} text-base"
                                    size="sm" wire:click="sortGuestGrades">
                                <span>{{ __('general.grade') }}</span>
                                <x-icon.chevron-small opacity="1"/>
                            </x-button.text-button>
                        </div>
                        <div class="divider"></div>
                        <div class="flex flex-col">
                            @forelse($guestList as $key => $guest)
                                <div class="relative w-full flex hover:font-bold p-5 rounded-10 base
                                    multiple-choice-question transition ease-in-out duration-150 focus:outline-none
                                    justify-between items-center hover:-mb-px cursor-pointer"
                                     wire:key="{{ $key }}"
                                     @if($this->canReviewTestTake())
                                     wire:click="continueAs('{{ $guest['uuid'] }}')"
                                     @endif
                                >
                                    <span>{{ $guest['name'] }}</span>
                                    @if($guest['rating'] != null)
                                    <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForTestParticipantRating($guest['rating']) !!}">
                                            {!! str_replace('.',',',round($guest['rating'], 1))!!}
                                    </span>
                                    @else
                                        <span>{{ __('student.no_grade') }}</span>
                                    @endif
                                </div>
                                <div class="h-px bg-blue-grey mx-2"></div>
                            @empty
                                <div class="mt-4">Geen profielen beschikbaar.</div>
                            @endforelse
                        </div>
                    @error('reviewing_time_has_expired')
                    <div class="mt-4 notification error stretched">
                        <span class="title">{{ $message }}</span>
                    </div>
                    @enderror
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
