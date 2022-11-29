<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary cursor-pointer relative', 'selid' => 'file-management-card']) }}
     wire:loading.class="hidden"
     wire:target="filters,clearFilters,$set"
     wire:click="openDetail(@js($file->uuid))"
     wire:key="file-card-{{ $file->uuid }}"
>
    <div class="flex flex-col">
        <div class="flex w-full items-start">
            <h3 class="flex-1 line-clamp-2 min-h-[64px]">{{ $file->test_name }}</h3>
            <span class="flex items-center gap-x-1">
                <span class="inline-flex capitalize">{{ $file->status->name }}</span>
                <span class="inline-flex rounded-sm border border-sysbase filemanagement-status label w-4 h-4"
                      style="--active-status-color: var(--{{ $file->status->colorcode }})"></span>
            </span>
        </div>
        <div class="flex w-full">
            <span class="flex-1">{{ '<toetsenbakker>' }}</span>
            <span class="flex note break-normal"
                  title="{{ $file->created_at->toDayDateTimeString() }}">{{ $file->created_at->toFormattedDateString() }}</span>
        </div>
        <div class="flex w-full">
            <span class="flex-1 line-clamp-1"
                  title="Behandelaar: {{ $file->handler?->name_full }}">{{ $file->handler?->name_full ?? '-' }}</span>
            <span class="">{{ $file->schoolLocation->name }}</span>
        </div>

        <div class="flex w-full italic">
            <span class="flex-1 line-clamp-2"
                  title="Docent: {{ $file->teacher?->name_full }}">{{ $file->teacher?->name_full }}</span>
            <span class="">{{ $file->subject }}</span>
        </div>
    </div>
</div>