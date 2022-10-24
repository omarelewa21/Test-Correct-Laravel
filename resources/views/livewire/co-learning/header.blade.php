<header id="header" class="h-[85px] fixed w-full content-center z-10 main-shadow bg-student @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->hasActiveMaintenance()) maintenance-header-bg @endif @if(\tcCore\Http\Helpers\GlobalStateHelper::getInstance()->isOnDeploymentTesting()) deployment-testing-marker @endif"
>
    <div class="py-2.5 px-6 flex h-full items-center justify-between">
        <div class="flex items-center space-x-4">
            {{-- back button--}}
            <x-button.back-round wire:click="back()"></x-button.back-round>
            {{-- CO-Learning: {test title} --}}
            <h4>{{ __('co-learning.co_learning') }}: </h4>
            <h1>{{ $testName }}</h1>
        </div>
        <div class="flex">
            {{-- "afronden" button --}}
            <x-button.student>
                <span>Afronden</span>
                <x-icon.checkmark></x-icon.checkmark>
            </x-button.student>
        </div>

    </div>
</header>
{{-- #e7d401 --}}