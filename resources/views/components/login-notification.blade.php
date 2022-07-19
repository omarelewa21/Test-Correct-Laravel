@props(['notificationTimeout' => 5000])
<x-stickers.congratulations2 class="hidden"/> {{-- svg wont load properly if it is not already existing on the page --}}
<div
        x-data="{
        messages: [],
        remove(message) {
            this.messages.splice(this.messages.indexOf(message), 1)
        },
    }"
        @notify.window="let message = $event.detail; console.log(messages); messages.push(message); setTimeout(() => { remove(message) }, {{ $notificationTimeout }})"
        class="fixed inset-0 flex flex-col items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:justify-start space-y-4"
        style="z-index:1000">

    <template x-for="message in messages" :key="messages.indexOf(message)">
        <div
                x-transition:enter="transform ease-out duration-300 transition"
                x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
                x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="max-w-md w-full shadow-lg rounded-lg pointer-events-auto"
        >
            <div class="rounded-lg shadow-xs overflow-hidden bg-offwhite border border-blue-grey  ">
                <div class="">
                    <div class="flex items-center relative">
                        <div class="flex-shrink-0 mt-2 mb-2 ml-2">
                            <template x-if="message.type == 'guest_success'">
                                <x-stickers.congratulations2 class="m-4 "/>
                            </template>
{{--                            <template x-if="message.type == 'error'">--}}
{{--                                <div class="fraud-detection rounded-full bg-all-red text-white flex justify-center items-center"--}}
{{--                                     style="width:30px;height:30px">--}}
{{--                                    <x-icon.exclamation style="transform: scale(1.5)"/>--}}
{{--                                </div>--}}
{{--                            </template>--}}
                        </div>
                        <div class="flex flex-col">
                            <div class="flex w-full flex-1 pt-0.5">
                                <h5 x-text="message.title" class=""></h5>
                            </div>
                            <div class="flex items-center w-full flex-1 pt-0.5">
                                <x-icon.checkmark/>
                                <h7 x-text="message.message" class="ml-2"></h7>
                            </div>
                        </div>

                        <div class="absolute top-6 right-6 flex-shrink-0 flex">
                            <button @click="remove(message)"
                                    class="inline-flex text-base focus:outline-none focus:text-primary transition ease-in-out duration-150">
                                <x-icon.close/>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>