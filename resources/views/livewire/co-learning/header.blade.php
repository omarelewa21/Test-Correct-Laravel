<header id="header" class="h-[var(--header-height)] fixed w-full content-center z-10 main-shadow bg-student @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->hasActiveMaintenance()) maintenance-header-bg @endif @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->isOnDeploymentTesting()) deployment-testing-marker @endif"
>
    <div class="py-2.5 px-6 flex h-full items-center justify-between">
        <div class="flex items-center space-x-4"
        x-data="{
        async back() {
            if(this.$store.answerFeedback.feedbackBeingEdited()) {
                return this.$store.answerFeedback.openConfirmationModal(this.$root, 'back');
            }

            this.$wire.back();
        }}"
             x-on:continue-navigation="Alpine.$data($el)[$event.detail.method]()"
        >
            <x-button.back-round x-on:click="await back()"></x-button.back-round>
            <h4>{{ __('co-learning.co_learning') }}: </h4>
            <h1>{{ $testName }}</h1>
        </div>
        <div class="flex">

        </div>
    </div>
</header>