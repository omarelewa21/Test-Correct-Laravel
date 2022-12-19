<div class="question-editor-header disabled z-50">
    <div class="flex items-center space-x-4 flex-1 relative mr-4">
        <button class="flex items-center justify-center rounded-full bg-white/20 w-10 h-10 rotate-svg-180 hover:scale-105 transition-transform"
                wire:click="saveAndRedirect"
                @click="$dispatch('store-current-question');"
                selid="back-btn"
        >
            <x-icon.arrow/>
        </button>
        <div class="flex flex-1" x-data="{testName: @entangle('testName')}">
            <h4 class="text-white truncate"
                :style="{'max-width': $el.parentElement.offsetWidth + 'px'}"
                x-text="testName"
                :title="testName"
            >

            </h4>
        </div>
    </div>

    <div class="flex space-x-6 items-center">
        @if($this->withDrawer)
            <div class="flex min-w-max space-x-2">
                {{--                <span class="text-sm">{{ trans_choice('cms.vraag', $questionCount['regular']) }}, {{ trans_choice('cms.group-question-count', $questionCount['group']) }}</span>--}}
            </div>

            <div class="flex space-x-2" x-data
                 @click="forceSyncEditors();$wire.saveIfDirty()"
            >
                <x-actions.test-delete variant="icon-button" :uuid="$this->testId"/>
                <x-actions.test-open-settings variant="icon-button" :uuid="$this->testId"/>
                <x-actions.test-open-preview variant="icon-button" :uuid="$this->testId"/>
                <livewire:actions.test-make-pdf variant="icon-button" :uuid="$this->testId"/>
                <livewire:actions.test-quick-take variant="icon-button" :uuid="$this->testId"/>
                <livewire:actions.test-plan-test variant="icon-button" :uuid="$this->testId"/>
            </div>
        @endif
    </div>
    <div class="absolute inset-0 z-50"
         x-data="{headerLoadingOverlay: false}"
         x-show="headerLoadingOverlay"
         @filepond-start.window="headerLoadingOverlay = true;"
         @filepond-finished.window="headerLoadingOverlay = false;"
    ></div>
</div>