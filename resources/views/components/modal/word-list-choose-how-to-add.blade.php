@props(['currentListName' => ''])
<div id="choose-add-list-modal"
     class="fixed inset-0 z-10"
     x-data="{
        showHowToAddListModal: false,
        newList: null,
     }"
     x-show="showHowToAddListModal"
     x-cloak
     x-trap.noscroll.inert="showHowToAddListModal"
     x-on:click.outside="showHowToAddListModal = false"
     x-on:open-modal="showHowToAddListModal = true; newList = $event.detail.list"
     x-transition:enter="ease-out duration-100"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-end="opacity-0 scale-90"
>
    <div x-show="showHowToAddListModal" class="fixed inset-0 transform" x-on:click="showHowToAddListModal = false"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-out duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-midgrey opacity-75 rounded-lg"></div>
    </div>
    <div x-show="showHowToAddListModal"
         class="relative top-1/2 flex flex-col pt-6 pb-5 px-10 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2 sm:mx-auto max-w-[600px]"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 sm:scale-95"
         x-transition:enter-end="opacity-100 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Hoe wil je toevoegen')?</h2>
            <x-button.close x-on:click="showHowToAddListModal = false" class="relative -right-3" />
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex w-full flex-col gap-4">
            <p class="text-lg">
                <span>@lang('cms.word-list-choose-how-to-add-start')</span>
                <span>‘<span x-text="newList?.name ?? ''"></span>’</span>
                <span>@lang('cms.word-list-choose-how-to-add-mid')</span>
                <span>‘{{ $currentListName ?? 'list 1' }}’?</span>
                <span>@lang('cms.word-list-choose-how-to-add-end')</span>
            </p>

            <div class="flex justify-between">
                <x-button.primary size="md"
                                  class="px-8"
                                  x-on:click="addList(newList, true); showHowToAddListModal = false"
                >
                    <x-icon.plus />
                    <span>@lang('cms.Apart toevoegen')</span>
                </x-button.primary>

                <x-button.cta size="md"
                              class="px-8"
                              x-on:click="addList(newList); showHowToAddListModal = false"
                >
                    <x-icon.plus />
                    <span>@lang('cms.Aan huidige toevoegen')</span>
                </x-button.cta>
            </div>
        </div>
    </div>

</div>
