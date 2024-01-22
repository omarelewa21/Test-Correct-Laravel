@props(['disabled' => false])
<div class="word-row contents relative"
     x-bind:class="'row-'+rowIndex"
>
    <span class="row-checkmark"
          x-on:change="toggleRow($event.target, rowIndex)"
    >
        <x-input.checkbox :$disabled />
    </span>

    <template x-for="(word, wordIndex) in row"
              :key="getTemplateWordKey(word, wordIndex)"
    >
        {{ $cell }}
    </template>
</div>