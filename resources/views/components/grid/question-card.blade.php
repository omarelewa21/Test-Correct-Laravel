<div {{ $attributes->merge(['class' => 'grid-card bg-white p-6 rounded-10 card-shadow hover:text-primary']) }}>
    <div class="flex w-full justify-between mb-2">
        <h3 class="line-clamp-2 min-h-[64px] @if(blank($question->title)) italic @endif" title="{{ $question->title }}">{!! $question->id !!} {{ $question->title ? $question->title : __('question.no_question_text') }}</h3>

        <x-icon.options class="text-sysbase"/>
    </div>
    <div class="flex w-full justify-between text-base mb-1">
        <div>
            <span class="bold">{{ $question->subject->baseSubject->name }}</span>
            <span>{{ $question->subject->name }}</span>
        </div>
        <div class="text-sm">
            <span class="note">Laatst gewijzigd:</span>
            <span class="note">{{ Carbon\Carbon::parse($question->updated_at)->format('d/m/\'y') }}</span>
        </div>
    </div>
    <div class="flex w-full justify-between text-base">
        <div>
            <span>Author</span>
        </div>

        <div class="relative" x-data="{checked:false}" @click="checked = !checked; $dispatch('checked', checked)">
            <input class="checkbox-custom"
                   name="checkbox" type="checkbox" :checked="checked"/>
            <label for="checkbox"
                   class="checkbox-custom-label">
                <svg width="13" height="13" xmlns="http://www.w3.org/2000/svg">
                    <path stroke="currentColor" stroke-width="3" d="M1.5 5.5l4 4 6-8" fill="none"
                          fill-rule="evenodd"
                          stroke-linecap="round"/>
                </svg>
            </label>
        </div>
    </div>
</div>