<div class="border-b flex w-full justify-between items-center py-2">
    <div class="flex items-center space-x-2.5">
        {{ $slot }}
    </div>
    <div>
        <label class="switch">
            <input type="checkbox" {{ $attributes->merge() }} value="1" autocapitalize="none" autocorrect="off"
                   autocomplete="off" spellcheck="false" class="verify-ok">
            <span class="slider round"></span>
        </label>
    </div>
</div>
