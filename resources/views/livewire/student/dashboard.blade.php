<div id="dashboard-body"
     class="mx-4 lg:mx-8 xl:mx-24 relative max-w-7xl w-full pb-10"
     x-data=""
     x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10 mx-4">
        <h1>{{ __('student.welcome_to_dashboard') }}</h1>
    </div>
    <div class="flex flex-col mx-4 space-y-4 xl:flex-row xl:space-x-4 xl:space-y-0">
        <div class="flex flex-col xl:w-4/6">
            <div class="flex flex-col space-y-4">
                <div>
                    <h4>{{ __('student.upcoming_tests_title') }}</h4>
                </div>
                <div class="content-section p-8">
                    @if($testTakes->count() == 0)
                        <p>{{ __('student.no_upcoming_tests') }}</p>
                    @else
                        <x-table>
                            <x-slot name="head">
                                <x-table.heading width="">{{ __('student.test') }}</x-table.heading>
                                <x-table.heading width="">{{ __('student.subject') }}</x-table.heading>
                                <x-table.heading width="120px"
                                                 textAlign="right">{{ __('student.take_date') }}</x-table.heading>
                                <x-table.heading width="100px">{{ __('student.type') }}</x-table.heading>
                                <x-table.heading width="125px"></x-table.heading>
                            </x-slot>
                            <x-slot name="body">
                                @foreach($testTakes as $testTake)
                                    <x-table.row wire:click="redirectToWaitingRoom({!!$testTake->getKey() !!})">
                                        <x-table.cell>{{ $testTake->test->name }}</x-table.cell>
                                        <x-table.cell>{!! $testTake->test->subject->name !!}</x-table.cell>
                                        <x-table.cell class="text-right">
                                            @if($testTake->time_start == \Carbon\Carbon::today())
                                                <span class="capitalize">{{ __('student.today') }}</span>
                                            @else
                                                {{ \Carbon\Carbon::parse($testTake->time_start)->format('d-m-Y') }}
                                            @endif
                                        </x-table.cell>
                                        <x-table.cell>
                                            <x-partials.test-take-type-label type="{{ $testTake->retake }}"/>
                                        </x-table.cell>
                                        <x-table.cell class="text-right">
                                            <x-partials.start-take-button :timeStart=" $testTake->time_start "
                                                                          :uuid="$testTake->uuid"/>
                                        </x-table.cell>
                                    </x-table.row>
                                @endforeach
                            </x-slot>
                        </x-table>
                    @endif
                </div>
                <div class="flex">
                    <x-button.primary class="ml-auto" type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'planned']) }}">
                        <span>{{ __('student.upcoming_tests') }}</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>

            <div class="flex flex-col space-y-4">
                <div>
                    <h4>{{ __('student.recent_grades') }}</h4>
                </div>
                <div class="content-section p-8">
                    @if($ratings->count() == 0)
                        <p>{{ __('student.no_recent_grades') }}</p>
                    @else
                        <x-table>
                            <x-slot name="head">
                                <x-table.heading width="">{{ __('student.test') }}</x-table.heading>
                                <x-table.heading width="">{{ __('student.subject') }}</x-table.heading>
                                <x-table.heading width="120px"
                                                 textAlign="right">{{ __('student.take_date') }}</x-table.heading>
                                <x-table.heading width="100px">{{ __('student.type') }}</x-table.heading>
                                <x-table.heading width="40px">{{ __('student.grade') }}</x-table.heading>
                            </x-slot>
                            <x-slot name="body">
                                @foreach($ratings as $rating)
                                    <x-table.row>
                                        <x-table.cell>{!! $rating->name !!}</x-table.cell>
                                        <x-table.cell>{!! $rating->subject_name !!}</x-table.cell>
                                        <x-table.cell class="text-right">
                                            @if($rating->time_start == \Carbon\Carbon::today())
                                                <span class="capitalize">{{ __('student.today') }}</span>
                                            @else
                                                <span>{{ \Carbon\Carbon::parse($rating->time_start)->format('d-m-Y') }}</span>
                                            @endif
                                        </x-table.cell>
                                        <x-table.cell>
                                            <x-partials.test-take-type-label type="{{ $rating->retake }}"/>
                                        </x-table.cell>
                                        <x-table.cell class="text-right">
                                        <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForRating($rating->rating) !!}">
                                            {!! str_replace('.',',',round($rating->rating, 1))!!}
                                        </span>
                                        </x-table.cell>
                                    </x-table.row>
                                @endforeach
                            </x-slot>
                        </x-table>
                    @endif
                </div>
                <div class="flex">
                    <x-button.primary class="ml-auto" type="link"
                                      href="{{ route('student.test-takes', ['tab' => 'grades']) }}">
                        <span>{{ __('student.see_grades') }}</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>
        </div>

        <div class="flex flex-1">
            <div class="flex flex-col space-y-4 w-full">
                <div>
                    <h4>{{ __('student.latest_messages') }}</h4>
                </div>
                <div class="content-section p-6 divide-y-2 ">
                    @forelse($messages as $message)
                        <div class="flex border-system-base">
                            <div class="flex flex-col flex-1 p-2 pt-4 text-md space-y-2">
                                <h6>{{ $message->subject }}</h6>
                                <p>{{ \Illuminate\Support\Str::limit($message->message, 200) }}</p>
                                <x-button.text-button>
                                    <span>{{ __('student.to_message') }}</span>
                                    <x-icon.arrow></x-icon.arrow>
                                </x-button.text-button>
                            </div>
                        </div>
                    @empty
                        <div class="flex border-system-base">
                            <div class="flex flex-col flex-1 p-2 pt-4 text-md space-y-2">
                                <p>{{ __('student.no_messages') }}</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>