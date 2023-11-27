<span x-data="initReadSpeakerLanguage()" style="position: relative; top:-16px;">
    <template x-for="language in languages">
    <button
            type="button"
            style="margin-top:-17px"
            x-bind:class="{
                'bg-white text-primary border-1 border-primary': !isCurrent(language),
                'text-xl text-white bg-primary ': isCurrent(language),
            }"
            x-on:click="selectLanguage(language)"
            class="px-2 py-1 rounded-full h-[40px] w-[40px]"
            x-text="language.substring(0,2)"
    ></button>
        </template>
</span>