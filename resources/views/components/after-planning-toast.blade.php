@props(['notificationTimeout' => 10000])
<div
    x-data="{
        message: null,
        take: null,
        link: null
    }"
    @after-planning-toast.window="
        message = $event.detail.message;
        link=$event.detail.link;
        take=$event.detail.take;
        setTimeout(() => { message=null }, {{ $notificationTimeout }});"
    class="fixed inset-0 flex flex-col items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:justify-start space-y-4"
    style="z-index:1000"
>
    <template x-if="message">
        <div
            x-transition:enter="transform ease-out duration-300 transition"
            x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto"
        >
            <div class="rounded-lg shadow-xs overflow-hidden border border-light-grey">
                <div class="p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p x-text="message" class="text-sm leading-5 font-medium text-gray-900"></p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex">
                            <button @click="message=null"
                                    class="inline-flex text-gray-400 focus:outline-none focus:text-gray-500 transition ease-in-out duration-150">
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd"
                                          d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                          clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center mt-3">
                        <div class="flex-shrink-0 w-6"></div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="cursor-pointer underline hover-light-color text-sm leading-5 font-medium hover-weight-600:hover" 
                                    x-clipboard='link'
                                    @click="message=null; $dispatch('notify', {message: '{{__('teacher.clipboard_copied')}}' })"
                            >
                            {{__('teacher.copyTestLink')}}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex"></div>
                    </div>
                    <div class="flex items-center mt-2">
                        <div class="flex-shrink-0 w-6"></div>
                        <div class="ml-3 w-0 flex-1 pt-0.5">
                            <p class="cursor-pointer underline hover-light-color text-sm leading-5 font-medium hover-weight-600:hover" @click="message=null; $wire.toPlannedTest(take)">
                                {{__('teacher.goToPlannedTests')}}
                            </p>
                        </div>
                        <div class="ml-4 flex-shrink-0 flex"></div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
