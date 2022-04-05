<div {{ $attributes->merge(['class' => 'bg-white p-6 rounded-10 card-shadow']) }}>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-ellipsis-two">{{ $title }}</h3>

        <x-icon.options/>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $baseSubject }}</span>
            <span>{{ $subject }}</span>
        </div>
        <div class="text-sm">
            <span class="note">Laatst gewijzigd:</span>
            <span class="note">{{ $updatedAt }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>{{ $author }}</span>
        </div>
    </div>

</div>