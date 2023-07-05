<span class="option | flex min-w-max items-center justify-between py-3 px-4 bold hover:bg-primary/5 active:bg-primary/10 hover:text-primary cursor-pointer "
      x-bind:class="active($el.dataset.value) && 'text-primary'"
      data-value="{{ $value }}"
      data-label="{{ $label }}"
      x-on:click.stop="activateSelect(@js($value), @js($label)); $root.dispatchEvent(new Event('change', {bubbles: true}))"
      {{ $attributes }}
>
    <span class="label">{{ $label }}</span>

    <x-icon.checkmark x-bind:class="active($el.parentElement.dataset.value) || 'invisible'"
                      class="ml-2"
    />
</span>