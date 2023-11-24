<span x-data="initReadSpeakerLanguage()">
    <template  x-for="language in languages">
    <button
            type="button"
            style="margin-top: -17px"
            x-bind:class="{'bg-white text-blue-500': language !== currentLanguage}"
            x-on:click="selectLanguage(language)"
            class="px-2 py-1 oval"
            x-text="language.substring(0,2)"
    ></button>
        </template>
</span>