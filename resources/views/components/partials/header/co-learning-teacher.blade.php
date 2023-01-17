<header id="header"
        class="h-[var(--header-height)] fixed top-0 left-0 w-full content-center z-10 main-shadow
        bg-gradient-to-r from-[var(--teacher-primary)] to-[var(--teacher-primary-light)] text-white
        @if($hasActiveMaintenance) maintenance-header-bg @endif
        @if($isOnDeploymentTesting) deployment-testing-marker @endif"
>
    <div class="py-2.5 px-6 flex h-full items-center justify-between">
        <div class="flex items-center space-x-4">
            <x-button.back-round wire:click="redirectBack()" class="bg-white/20 hover:text-white"></x-button.back-round>
            <h6 class="text-white">{{ __('co-learning.co_learning') }}: </h6>
            <h4 class="text-white">{{ $testName }}</h4>
        </div>
        <div class="flex">
            <div class="text-right text-[14px] mr-4">
                {{ __('co-learning.questions_being_discussed') }}<br>
                {{ $discussionTypeTranslation }}
            </div>
            <div class="flex items-center">
                <x-button.cta :disabled="!$atLastQuestion"
                        @class(['opacity-40' => !$atLastQuestion])
                        wire:click.prevent="finishCoLearning"
                >
                    {{ __('co-learning.complete') }}
                    <x-icon.checkmark class="ml-2"/>
                </x-button.cta>
            </div>
        </div>
    </div>
</header>