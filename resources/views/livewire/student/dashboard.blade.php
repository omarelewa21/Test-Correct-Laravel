<div id="dashboard-body"
     class="px-4 lg:px-8 xl:px-24 relative w-full pb-10"
     x-data="{showKnowledgebankAppNotificationModal: @entangle('showKnowledgebankAppNotificationModal')}"
     x-init="addRelativePaddingToBody('dashboard-body'); makeHeaderMenuActive('student-header-dashboard');"
     x-cloak
     x-on:resize.window.debounce.200ms="addRelativePaddingToBody('dashboard-body')"
     wire:ignore.self
>
    <div class="flex my-10">
        <h1>{{ __('student.welcome_to_dashboard') }}</h1>
    </div>
    @if($this->showAppVersionMessage())
        <div class="flex flex-col w-full justify-center items-center mb-4 max-w-4xl mx-auto">
            <div class="flex flex-col notification stretched px-6 w-full main-shadow
                    @if(session()->get('TLCVersioncheckResult') == 'NOTALLOWED') error @else warning @endif
                    ">
                <div class="title flex items-center space-x-2.5">
                    <x-icon.warning/>
                    <span>{{ __('student.warning') }}</span>
                </div>
                <div class="body flex">
                    <span>
                        @if(session()->get('TLCVersioncheckResult') == 'NOTALLOWED')
                            {{ __('student.app_not_allowed') }}
                        @else
                            @if($this->needsUpdateDeadline)
                                {{ __('student.app_needs_update_deadline', ['date' => $this->needsUpdateDeadline]) }}
                            @else
                                {{ __('student.app_needs_update') }}
                            @endif
                        @endif
                        <button class="bold inline-flex items-center space-x-1 hover:underline"
                                @click="showKnowledgebankAppNotificationModal = true">
                            <span>{{ __('student.read_more') }}</span>
                            <x-icon.arrow-small/>
                        </button>
                    </span>
                </div>
            </div>
        </div>
    @endif
    @if($infos)
        <div class="flex flex-col w-full justify-center items-center mb-8 max-w-4xl mx-auto">
            @foreach($infos as $info)
                <div class="flex flex-col notification info stretched px-6 w-full main-shadow">
                    <div class="title flex items-center">
                        <span>{!! $info['title_'.session()->get('locale')] !!}</span>
                    </div>
                    <div class="body">
                        <span>{!! $info['content_'.session()->get('locale')] !!}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="flex flex-col space-y-4 xl:flex-row xl:space-x-4 xl:space-y-0">
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
                                <x-table.heading width="120px">{{ __('student.type') }}</x-table.heading>
                                <x-table.heading width="125px"></x-table.heading>
                            </x-slot>
                            <x-slot name="body">
                                @foreach($testTakes as $testTake)
                                    <x-table.row wire:click="redirectToWaitingRoom('{!!$testTake->uuid !!}')">
                                        <x-table.cell>{{ $testTake->test_name }}</x-table.cell>
                                        <x-table.cell>{!! $testTake->subject_name !!}</x-table.cell>
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
                                        <x-table.cell class="text-right" buttonCell>
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
                    @if($testParticipants->count() == 0)
                        <p>{{ __('student.no_recent_grades') }}</p>
                    @else
                        <x-table>
                            <x-slot name="head">
                                <x-table.heading width="">{{ __('student.test') }}</x-table.heading>
                                <x-table.heading width="">{{ __('student.subject') }}</x-table.heading>
                                <x-table.heading width="130px"
                                                 textAlign="right">{{ __('student.take_date') }}</x-table.heading>
                                <x-table.heading width="120px">{{ __('student.type') }}</x-table.heading>
                                <x-table.heading width="70px">{{ __('student.grade') }}</x-table.heading>
                            </x-slot>
                            <x-slot name="body">
                                @foreach($testParticipants as $testParticipant)
                                    <x-table.row>
                                        <x-table.cell>{!! $testParticipant->name !!}</x-table.cell>
                                        <x-table.cell>{!! $testParticipant->subject_name !!}</x-table.cell>
                                        <x-table.cell class="text-right">
                                            @if($testParticipant->time_start == \Carbon\Carbon::today())
                                                <span class="capitalize">{{ __('student.today') }}</span>
                                            @else
                                                <span>{{ \Carbon\Carbon::parse($testParticipant->time_start)->format('d-m-Y') }}</span>
                                            @endif
                                        </x-table.cell>
                                        <x-table.cell>
                                            <x-partials.test-take-type-label type="{{ $testParticipant->retake }}"/>
                                        </x-table.cell>
                                        <x-table.cell class="text-right">
                                        <span class="px-2 py-1 text-sm rounded-full {!! $this->getBgColorForTestParticipantRating($testParticipant->rating) !!}">
                                            {!! str_replace('.',',',round($testParticipant->rating, 1))!!}
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
                                      href="{{ route('student.test-takes', ['tab' => 'graded']) }}">
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
                                @if(strlen($message->message) > 200)
                                    <button class="flex text-base items-center bold hover:underline space-x-1" wire:click="readMessages()">
                                        <span>{{ __('student.read_more') }}</span>
                                        <x-icon.arrow-small/>
                                    </button>
                                @endif
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
                <div class="flex">
                    <x-button.primary class="ml-auto" wire:click="readMessages()">
                        <span>{{ __('student.messages') }}</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>
        </div>
    </div>
    @if($this->showKnowledgebankAppNotificationModal)
    <x-modal.iframe wire:model="showKnowledgebankAppNotificationModal"
                    url="https://support.test-correct.nl/knowledge/melding-verouderde-versie"
                    maxWidth="7xl"
    />
    @endif
</div>