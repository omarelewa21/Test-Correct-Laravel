@props(['columnHeads', 'disabled' => false, 'emptySelectPlaceholder' => ''])
<div class="grid-head-container row-head contents">
    <div class="grid-head head-checkmark"
         x-on:change="toggleAll($event.target)"
    >
        <x-input.checkbox :$disabled />
    </div>
    <template x-for="(type, headerIndex) in cols">
        <div class="grid-head"
             x-bind:data-header-column="headerIndex"
             x-on:change="columnValueUpdated(headerIndex, $event.target.dataset.value)"
        >
            <x-input.select :placeholder="$emptySelectPlaceholder"
                            class="!min-w-[130px]"
                            :empty-option="true"
                            :$disabled
            >
                @foreach($columnHeads as $value => $label)
                    <x-input.option :value="$value" :label="$label" />
                @endforeach
            </x-input.select>
        </div>
    </template>
</div>