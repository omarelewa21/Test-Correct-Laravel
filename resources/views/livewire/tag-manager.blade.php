<div class="relative flex flex-wrap items-center text-base"
     x-data="{query: @entangle('query'), selectedTags: @entangle('selectedTags')}"
     x-init="$watch('selectedTags', value => $wire.emitUp('new-tags-for-question', {'tags':value}));"
>
    <div class="flex mb-2">
        @forelse($selectedTags as $id => $name)
                <x-button.secondary class="mr-2.5" wire:click="removeSelectedTag('{{ $id }}')">
                    <span>{{ $name }}</span>
                    <x-icon.close-small/>
                </x-button.secondary>
        @empty
            <span></span>
        @endforelse
    </div>
    <div class="flex relative mb-2">
        <div class="flex flex-row-reverse z-10">
            <x-button.secondary style="box-shadow: none;" class="items-center pr-2 pl-6 -ml-4 relative hide-shadow" wire:click="addQueryAsTag()" selid="add-tag-btn">
                <x-icon.plus/>
            </x-button.secondary>
            <x-input.text
                    type="text"
                    class="z-10"
                    placeholder="{{ __('cms.Zoek tags ...') }}"
                    wire:model="query"
                    wire:keydown.escape="resetValues()"
                    wire:keydown.tab="resetValues()"
                    wire:keydown.arrow-up="decrementHighlight()"
                    wire:keydown.arrow-down="incrementHighlight()"
                    wire:keydown.enter="selectTag()"
                    selid="tag-input"
                    @click.outside="if (query) { query = ''}"
            />
        </div>
        @if(!empty($query))
            <div class="absolute z-20 w-full bg-white shadow-lg list-group top-10 bg-white rounded-b-10 cursor-pointer overflow-hidden ">
                @if(!empty($tags))
                    @foreach($tags as $i => $tag)
                        <div class="p-2 {{ $highlightIndex === $i ? 'bg-off-white' : '' }} bg-primary-hover hover:text-white" wire:click="selectTag({{ $i }})">
                            {{ $tag['name'] }}
                        </div>
                    @endforeach
                @endif
            </div>
        @endif
    </div>

</div>
