<span class="flex items-center gap-x-1">
    <span class="inline-flex capitalize">{{ $status->name }}</span>
    <span class="inline-flex rounded-sm border border-sysbase filemanagement-status label w-4 h-4"
          style="--active-status-color: var(--{{ $status->colorcode }})"></span>
</span>