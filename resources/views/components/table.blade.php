{{--
-- Important note:
--
-- This template is based on an example from Tailwind UI, and is used here with permission from Tailwind Labs
-- for educational purposes only. Please do not use this template in your own projects without purchasing a
-- Tailwind UI license, or they’ll have to tighten up the licensing and you’ll ruin the fun for everyone.
--
-- Purchase here: https://tailwindui.com/
--}}

<div class="align-middle min-w-full sm:rounded-lg">
    <table class="min-w-full">
        <thead class="border-b-2 border-system-base">
            <tr>
                {{ $head }}
            </tr>
        </thead>

        <tbody class="bg-white overflow-auto">
            {{ $body }}
        </tbody>
    </table>
</div>
