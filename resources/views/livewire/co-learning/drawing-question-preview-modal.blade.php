<x-partials.modal.preview>
    <div class="w-full h-full"
         x-data="{
         zoomOut: () => {
                if(this.percentage <= 12.5) { return; }
                if(this.percentage <= 25) {
                    this.percentage = 12.5;
                } else {
                    this.percentage = this.percentage - 25;
                }
                drawingImg.style.width = this.percentage.toString() + '%';
             },
             zoomIn: () => {
                 this.percentage = this.percentage + 25;
                 drawingImg.style.width = this.percentage.toString() + '%';
             }
         }"
         x-init="drawingImg = $refs.drawingAnswer;
                 percentage = 100;
                 drawingImg.style.width = percentage.toString() + '%';
         ">
        <div class="w-full h-full overflow-auto flex flex-col items-center align-center justify-center">
            <img src="{{ $this->imgSrc }}" style="max-width: 300%"
                 class="w-full bg-white" alt="Drawing answer"
                 x-ref="drawingAnswer">
        </div>
        <div style="position: absolute; bottom: 19px; right: 67px;"
             @click="zoomOut()"
        >
            <x-button.icon-circle>
                <x-icon.min/>
            </x-button.icon-circle>
        </div>
        <div style="position: absolute;  bottom: 19px; right: 19px;"
             @click="zoomIn()"
        >
            <x-button.icon-circle>
                <x-icon.plus/>
            </x-button.icon-circle>
        </div>
    </div>
    <x-slot name="icon">
        <x-icon.image/>
    </x-slot>
</x-partials.modal.preview>