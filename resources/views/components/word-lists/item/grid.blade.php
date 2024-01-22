<div class="flex relative">

    <div class="relation-question-grid-container |">
        <div x-bind:id="'relation-question-grid-' + wordListIndex"
             class="relation-question-grid | "
             wire:ignore
        >
            {{ $head }}
            <template x-for="(row, rowIndex) in rows"
                      x-bind:key="getTemplateRowKey(row, rowIndex)"
            >
                {{ $row }}
            </template>

            {{ $slot }}
        </div>
        <div class="relation-grid-sticky-pseudo"></div>
    </div>

    <div class="absolute bottom-2 flex flex-col items-center w-full gap-2 pointer-events-none">
        <template x-for="(message, name) in errorMessages" :key="name">
            <div class="notification error without-message pointer-events-auto cursor-default"
                 x-data="{
                                                    showPopover: false,
                                                    init() {
                                                        this.$nextTick(() => this.showPopover = true)
                                                    },
                                                    transitionOut() {
                                                        this.showPopover = false

                                                        setTimeout(() => this.removeErrorMessage(this.name), 500)
                                                    },
                                                 }"
                 x-show="showPopover"
                 x-transition
                 x-on:hide-error="transitionOut()"
                 x-on:click.stop="transitionOut()"
            >
                <div class="title" x-text="message"></div>
            </div>
        </template>
    </div>
</div>