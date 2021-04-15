@props(['timeStart', 'status', 'uuid'])

@if($timeStart == \Carbon\Carbon::today())
    @if($status > \tcCore\TestTakeStatus::STATUS_TAKING_TEST)
        <x-button.cta size="sm" disabled class="disabled">Maken</x-button.cta>
    @else
        <x-button.cta size="sm" wire:click="startTestTake('{{ $uuid }}')">Maken</x-button.cta>
    @endif
@else
    <span class="italic">gepland</span>
@endif