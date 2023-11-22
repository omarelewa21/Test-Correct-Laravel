<span x-data="initReadSpeakerLanguage()">
    <template  x-for="language in languages">
    <button
            type="button"
            x-bind:class="{'bg-blue-500 text-white': language === currentLanguage, 'bg-white text-blue-500': language !== currentLanguage}"
            x-on:click="changeLanguage(language)"
            class="px-2 py-1 rounded-md"
    >NL</button>
        </template>
</span>