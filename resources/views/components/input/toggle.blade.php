<label @class(['switch', $attributes->get('class')])>
   <input type="checkbox" {{ $attributes->except('class') }} value="1"  autocapitalize="none" autocorrect="off" autocomplete="off" spellcheck="false" class="verify-ok">
    <span class="slider round"></span>
</label>
