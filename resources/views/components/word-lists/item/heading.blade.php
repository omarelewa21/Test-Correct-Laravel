@props(['disabled' => false])
<div class="flex w-full items-center gap-6">
    <div class="flex flex-1 gap-2 items-center">
        <h7 class="flex capitalize"
            x-bind:class="{'!text-allred': errorState}">
            @lang('cms.woordenlijst')
        </h7>
        <x-input.text x-model="list.name" class="flex flex-1" :$disabled />
        <template x-if="errorState">
                                    <span class="rounded-full h-6 w-6 bg-allred flex items-center justify-center text-white">
                                        <x-icon.exclamation />
                                    </span>
        </template>
    </div>
    <div class="flex gap-2 items-center"
         x-bind:class="{'rotate-svg-90': expanded}"
    >
        <span class="note text-sm"><span x-text="relationCount"></span> @lang('cms.relaties')</span>
        <div class="group cursor-pointer">
            <x-button.collapse-chevron x-on:click="expanded = !expanded" />
        </div>
    </div>
</div>