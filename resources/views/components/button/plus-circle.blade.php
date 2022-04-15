<div {{ $attributes->merge(['class' => 'flex px-6 py-2.5 space-x-2.5 cursor-pointer transition hover:text-primary hover:bg-offwhite text-lg']) }}>
    <x-icon.plus-in-circle/>
    <button class="bold">{{ $slot }}</button>
</div>