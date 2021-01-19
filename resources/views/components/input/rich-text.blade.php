{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

<div wire:ignore>

    <textarea class="border-gray-300 border-2" name="editor1" id="editor1" rows="10" cols="80">
        {{ $slot }}
    </textarea>
</div>
