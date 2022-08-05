<div style="padding-left: 32px; padding-right: 32px;">
    <div class="group-question-container">
        @if($groupStart)
            <div>
                <div class="group-question-icon1">
                    <svg class='inline-block' width="21" height="21" xmlns="http://www.w3.org/2000/svg">
                        <g stroke="#ffffff" fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-width="3"
                           transform="rotate(90 9.75 10.5) scale(1.5 1.5)">
                            <path d="M1.5 6.5h10M6.5 1.5l5 5-5 5"/>
                        </g>
                    </svg>

                </div>

                <div style="display: inline-block; font-size: 20pt;">
                    {{ $question->name }}
                </div>
            </div>
            <div class="group-question-description">
                {!! $description !!}
            </div>
        @else
            <div style="margin-bottom: 0.5rem">
                <div class="group-question-icon2" >
                    <svg xmlns="http://www.w3.org/2000/svg" width="21" height="22" viewBox="0 0 21 19">
                        <g fill="none" fill-rule="evenodd" stroke-linecap="round">
                            <g stroke="#ffffff" stroke-width="3" transform="rotate(90 10 9.5) scale(1.25 1.25)">
                                <g>
                                    <g>
                                        <g>
                                            <path d="M6.5 2.5L11.5 7.5 6.5 12.5M15.5 1.5L15.5 13.5M1.5 7.5L10.5 7.5"
                                                  transform="translate(-1303 -102) translate(1264 70) translate(32) translate(7 32)"/>
                                        </g>
                                    </g>
                                </g>
                            </g>
                        </g>
                    </svg>
                </div>

                <div style="display: inline-block; font-size: 20pt; ">
                    {{ __('test-pdf.end-of-group-question') }} {{ $question->name }}
                </div>
            </div>

        @endif
    </div>
    @if($groupStart && $question->questionAttachments)
        <livewire:test-print.question-attachments :attachments="$question->attachments" :attachment_counters="$this->attachment_counters"/>
    @endif
</div>