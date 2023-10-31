<div id="dirty-modal"
     class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-[101]"
     x-data="{dirtyModal: false, toExisting: false, isGroup: false, leavingTest: false, data: {}}"
     x-init="$watch('dirtyModal', (value) => {
                if(!value) return;
                $store.cms.processing = false;
             });
            deleteAction = () => {
                dirtyModal = false;
                $wire.set('forceOpenNewQuestion', true);
                if(toExisting) {
                    $wire.continueToNextQuestion()
                    return;
                }
                if(isGroup) {
                    $dispatch('continue-to-add-group')
                    return;
                }

                if (leavingTest) {
                    $wire.returnToTestOverview();
                    return;
                }

                $dispatch('continue-to-new-slide', data)


            }
            continueAction = () => {
                dirtyModal = false;
                $dispatch('hide-backdrop-if-active')
                $wire.validateFromDirtyModal()
            }
     "
     x-show="dirtyModal"
     x-cloak
     x-transition:enter="ease-out duration-100"
     x-transition:enter-start="opacity-0 scale-90"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-start="opacity-100 scale-100"
     x-transition:leave-end="opacity-0 scale-90"
     x-on:show-dirty-question-modal.window="dirtyModal = true; toExisting = $event.detail.goingToExisting ?? false; isGroup = $event.detail.group ?? false; leavingTest = $event.detail.leavingTest ?? false; data = $event.detail.data ?? {}"
>
    <div x-show="dirtyModal" class="fixed inset-0 transform " x-on:click="dirtyModal = false"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-midgrey opacity-75"></div>
    </div>
    <div x-show="dirtyModal"
         class="relative top-1/2 flex flex-col py-5 px-7 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2  max-w-2xl sm:mx-auto"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
         x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
        <div class="px-2.5 flex justify-between items-center mt-2">
            @if($new)
                <h2 class="">{{ __('cms.Nieuwe item incompleet', ['item' => $item, 'new' => trans_choice('cms.nieuwe', 1)]) }}</h2>
            @else
                <h2 class="">{{ __('cms.item incompleet', ['item' => $item]) }}</h2>
            @endif
            <x-icon.close class="cursor-pointer hover:text-primary" @click="dirtyModal = false"/>
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex flex-1 h-full w-full px-2.5 body1 mb-5 space-x-2.5 ">
            <div class="flex flex-1 flex-col ">
                @if($new)
                    <span>{{ __('cms.new_question_incomplete_text', ['item' => $item, 'new' => $new ? __('cms.nieuwe') : '']) }}</span>
                @else
                    <span class="">{{ __('cms.question_incomplete_text', ['item' => $item]) }}</span>
                @endif
                <div class="flex w-full justify-end mt-4 space-x-4 items-center">
                    <x-button.text @click="deleteAction()">
                        <span>{{ $new ? __('cms.Verwijderen') : __('cms.Wijzigingen niet opslaan')}}</span>
                    </x-button.text>
                    <x-button.primary @click="continueAction()" size="md">
                        <span>{{ __('cms.Aanvullen') }}</span>
                        <x-icon.chevron/>
                    </x-button.primary>
                </div>
            </div>
        </div>
    </div>
</div>
