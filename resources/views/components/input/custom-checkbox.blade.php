<div {{ $attributes->merge(['class' => 'relative']) }}
     x-data="{checked: {{ $checked ? 'true' : 'false'}} }"
     x-on:click="checked = !checked;"
     >
    <input class="checkbox-custom"
           name="checkbox"
           type="checkbox"
           :checked="checked"
    />
    <label for="checkbox"
           class="checkbox-custom-label">
        <svg width="13" height="13" xmlns="http://www.w3.org/2000/svg">
            <path stroke="currentColor" stroke-width="3" d="M1.5 5.5l4 4 6-8" fill="none"
                  fill-rule="evenodd"
                  stroke-linecap="round"/>
        </svg>
    </label>
</div>