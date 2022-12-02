@props([
    'generalStats'
])

@foreach(['tests', 'assignments'] as $kind)
    <div class="flex-1 gap-4 flex flex-col">
        <div class="flex flex-col">
            <span>{{ __('student.aantal '. $kind. ' gemaakt')}}</span>
            @if( $generalStats[$kind.'_taken'] == 0)
                <span class="bold note">{{ $generalStats[$kind.'_taken'] }}</span>
            @else
                <span class="bold">{{ $generalStats[$kind.'_taken'] }}</span>
            @endif
        </div>
        <div class="flex flex-col">
            <span>{{ __('student.gemiddelde p-waarde') }}</span>
            <div class="inline-block">
                @if( $generalStats[$kind.'_taken'] == 0)
                    <span class="note">
                        {{  __('student.o.b.v. aantal vragen', ['count'=> 0]) }}
                        <span class="bold px-0.5 note">P -.-- </span>
                    </span>
                @else
                    <span>
                        {{ __('student.o.b.v. aantal vragen', ['count'=> $generalStats[$kind.'_questions']]) }}
                        <span
                            class="bold px-0.5">P {{ number_format($generalStats[$kind.'_pvalue_average'], 2) }}</span>
                    </span>
                @endif
                <x-user-p-value-indicator :p-value="$generalStats[$kind.'_pvalue_average']"
                                          disabled="{!! (bool) ($generalStats[$kind.'_taken'] == 0) !!}"></x-user-p-value-indicator>
            </div>
        </div>
        <div class="flex flex-col">
            <div>{{ __('student.gemiddeld cijfer') }}</div>
            <div class="flex gap-4 items-center">
                <x-mark-badge :rating="$generalStats[$kind.'_rating_average']"></x-mark-badge>
                @if (!$this->viewingAsTeacher())
                    <x-button.text-button wire:click="showGrades">
                        <span class="bold">{{ __('student.Bekijk cijferlijst') }}</span>
                        <x-icon.arrow/>
                    </x-button.text-button>
                @endif
            </div>
        </div>
    </div>
@endforeach
<div class="flex-1 gap-4 flex flex-col">
    <div class="flex flex-col" wire:ignore x-data="{
        active:false,
         async init() {
           let ready = await $wire.getFirstActiveForGeneralGraphTaxonomy();
                if (ready !== false) {
                  $dispatch('active-changed', { id: ready })
                }
            }
         }"
    >
        @foreach($this->taxonomies as $key=> $taxonomy)
            <div
                x-data="expandableGraphForGeneral({{ $key }}, '{{ $this->taxonomyIdentifier}}', '{{ $taxonomy['name'] }}')"
                x-on:active-changed.window="if (id === $event.detail.id)  { expanded = true}"
                x-on:click="expanded = !expanded"
                class="cursor-pointer ml-10"
            >
                            <span :class="{ 'rotate-svg-90' : expanded }">
                                <x-icon.chevron></x-icon.chevron>
                            </span>
                <span>{{ __('student.Taxonomy') }} {{ $taxonomy['name'] }} {{__('student.Methode') }}</span>
                <div x-show="expanded">
                    <div wire:loading
                         wire:target="getDataForGeneralGraph('{{ $this->taxonomyIdentifier }}', '{{ $taxonomy['name'] }}')"
                    >
                        loading
                    </div>
                    <div :id="containerId" style="height: {{ $taxonomy['height'] }}">
                        <div x-show="showEmptyState" class="relative">
                            <x-empty-taxonomy-graph></x-empty-taxonomy-graph>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
