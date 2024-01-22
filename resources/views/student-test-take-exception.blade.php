<x-layouts.app>
    <div class="w-full flex py-12">
    <div class="w-full bg-white rounded-10 main-shadow flex flex-col items-center justify-center gap-4">
        <h3>@lang('student.Er is iets misgegaan met het ophalen van de vragen')...</h3>

        <x-button.cta onclick="location.reload()" size="md">
            <span>@lang('student.Probeer opnieuw')</span>
        </x-button.cta>
    </div>
    </div>
</x-layouts.app>