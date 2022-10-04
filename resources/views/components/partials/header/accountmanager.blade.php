
    <x-button.text-button type="link" href="{{ route('auth.temporary-login.to-cake') }}">
        <span>Dashboard</span>
    </x-button.text-button>
    <x-button.text-button type="link" href="{{ route('uwlr.grid') }}">
        <span>UWLR Grid</span>
    </x-button.text-button>
    <x-button.text-button type="link" href="{{ route('account-manager.school-locations') }}">
        <span>{{ __('school_location.school_locations') }}</span>
    </x-button.text-button>
    <x-button.text-button type="link" href="{{ route('account-manager.schools') }}">
        <span>{{ __('school.schools') }}</span>
    </x-button.text-button>
