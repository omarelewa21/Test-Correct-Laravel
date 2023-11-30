<div id="add-list-modal"
     class="fixed inset-0 z-10"
     x-data="{
        showAddListModal: false,
        dataset: {
            new: {
                title: @js(__('cms.Nieuwe woordenlijst')),
                description: @js(__('cms.add_list_modal_new_list_description')),
                action: ((component) => {
                    document.querySelector('.word-list-container').dispatchEvent(new CustomEvent('add-new'));
                    component.$nextTick(() => component.showAddListModal = false);
                    })
            },
            existing : {
                title: @js(__('cms.Bestaande woordenlijst')),
                description: @js(__('cms.add_list_modal_existing_list_description')),
                action: ((component) => {
                    document.querySelector('.word-list-container').dispatchEvent(new CustomEvent('open-add-existing-panel'));
                    component.$nextTick(() => component.showAddListModal = false);
                    })
            },
            upload: {
                title: @js(__('cms.Upload bestand')),
                description: @js(__('cms.add_list_modal_upload_list_description')),
                action:  ((component) => {
                    document.querySelector('.word-list-container').dispatchEvent(new CustomEvent('upload'))
                    component.$nextTick(() => component.showAddListModal = false);
                    })
            }
        }
     }"
     x-show="showAddListModal"
     x-cloak
     x-trap.noscroll.inert="showAddListModal"
     x-on:click.outside="showAddListModal = false"
     x-on:open-modal="showAddListModal = true;"
     x-transition:enter="ease-out duration-100"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-end="opacity-0 scale-90"
>
    <div x-show="showAddListModal" class="fixed inset-0 transform" x-on:click="showAddListModal = false"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-out duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-midgrey opacity-75 rounded-lg"></div>
    </div>
    <div x-show="showAddListModal"
         class="relative top-1/2 flex flex-col pt-6 pb-10 px-10 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2 sm:mx-auto max-w-[720px]"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 sm:scale-95"
         x-transition:enter-end="opacity-100 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Hoe wil je een woordenlijst toevoegen')?</h2>
            <x-button.close x-on:click="showAddListModal = false" class="relative -right-3" />
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex w-full">
            <div class="w-full grid grid-cols-2 gap-4 select-none">
                <template x-for="(set, key) in dataset">
                    <div class="col-span-1 rounded-10 border-2 border-bluegrey flex pl-6 pr-2 pt-2 pb-4 hover:text-primary hover:bg-offwhite transition-colors group/grid-item cursor-pointer active:bg-primary/5 hover:shadow-md"
                         x-on:click="set.action($root._x_dataStack[0])"
                    >
                        <div class="sticker pt-1">
                            <x-stickers.wordlist-new x-show="key === 'new'" x-cloak />
                            <x-stickers.wordlist-add-existing x-show="key === 'existing'" x-cloak />
                            <x-stickers.wordlist-upload x-show="key === 'upload'" x-cloak />
                        </div>
                        <div class="flex flex-col text pt-1.5 ml-4 mr-1">
                            <span class="bold" x-text="set.title"></span>
                            <span class="note text-sm group-hover/grid-item:text-primary"
                                  x-text="set.description"></span>
                        </div>
                        <div class="check relative -top-1">
                            <x-icon.checkmark
                                    class="text-white group-hover/grid-item:text-primary/20 transition-colors" />
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
