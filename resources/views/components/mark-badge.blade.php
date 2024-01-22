<span x-data="markBadge(@js($rating))"
      x-on:update-mark-badge="setNewRating($event.detail.rating)"
      @class(["mark-badge", $attributes->get('class')])
      {{ $attributes->except('class') }}
>
    <span class="inline-flex items-center justify-center min-w-[32px] min-h-[32px] text-sm rounded-full"
          x-bind:class="{
                    'border border-mid-grey bg-white': !hasValue(),
                    'bg-cta-primary text-white': hasValue() && parseFloat(markBadgeRating) > 5.5,
                    'bg-all-red text-white': hasValue() && parseFloat(markBadgeRating) < 5.5,
                    'bg-orange base': hasValue() && parseFloat(markBadgeRating) === 5.5,
                }"
          x-bind:title="hasValue() ? displayMarkBadgeRating : @js(__('student.no_grade'))"
    >
        <span class="inline-flex px-2"
              x-text="displayMarkBadgeRating"></span>
    </span>
</span>