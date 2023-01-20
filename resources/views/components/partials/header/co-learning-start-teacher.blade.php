<header id="co-learning-teacher-start-header"
        class="h-[var(--header-height)] fixed top-0 left-0 w-full content-center text-white z-50
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

        </div>
    </div>
</header>