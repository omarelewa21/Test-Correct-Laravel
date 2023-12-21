<div id="compile-list-upload-modal"
     class="fixed inset-0 z-10"
     x-data="{
        showCompileListUploadModal: false,
        list: null,
        open(event) {
            this.list = event?.detail?.list ?? null;
            this.showCompileListUploadModal = true;
        },
        close() {
            this.showCompileListUploadModal = false;
            this.list = null
        },
        fileInput(event) {

            let selector = '.word-list-container';
            if (this.list) {
                selector += ` [data-list-uuid='${this.list}']`;
            }
            document.querySelector(selector).dispatchEvent(
                new CustomEvent(`handle-upload`, { detail: { file: event.target.files[0] } })
            );

            this.close();
        },
     }"
     x-show="showCompileListUploadModal"
     x-cloak
     x-trap.noscroll.inert="showCompileListUploadModal"
     x-on:click.outside="close()"
     x-on:open-modal="open($event)"
     x-transition:enter="ease-out duration-100"
     x-transition:enter-end="opacity-100 scale-100"
     x-transition:leave="ease-in duration-100"
     x-transition:leave-end="opacity-0 scale-90"
>
    <div x-show="showCompileListUploadModal" class="fixed inset-0 transform" x-on:click="close()"
         x-transition:enter="ease-out duration-100"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-out duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-midgrey opacity-75 rounded-lg"></div>
    </div>
    <div x-show="showCompileListUploadModal"
         class="relative top-1/2 flex flex-col pt-6 pb-5 px-10 bg-white rounded-10 overflow-hidden shadow-xl transform -translate-y-1/2 sm:mx-auto max-w-[600px]"
         x-transition:enter="ease-out duration-200"
         x-transition:enter-start="opacity-0 sm:scale-95"
         x-transition:enter-end="opacity-100 sm:scale-100"
         x-transition:leave="ease-in duration-100"
         x-transition:leave-start="opacity-100 sm:scale-100"
         x-transition:leave-end="opacity-0 sm:scale-95"
    >
        <div class="flex justify-between items-center">
            <h2>@lang('cms.Importeer uit Excel')</h2>
            <x-button.close x-on:click="close()" class="relative -right-3" />
        </div>
        <div class="divider mb-5 mt-2.5"></div>
        <div class="flex flex-col w-full">
            <span class="text-lg mb-2">@lang('cms.excel_import_modal_explainer')</span>

            <div class="w-full grid grid-cols-2 gap-4 mb-8">
                <div>
                    <span>@lang('cms.Voorwaarden')</span>
                    <ul>
                        <li>@lang('cms.Bestand met 1 tabblad')</li>
                        <li>{{ __('cms.Max. 6 kolommen', ['columns' => count(\tcCore\Http\Enums\WordType::cases())]) }}</li>
                        <li>@lang('cms.Geen kolomkoppen')</li>
                        <li>@lang('cms.Min. taalvak taal kolom met')</li>
                        <li>@lang('cms.Min. vertaaltaal kolom')</li>
                    </ul>
                </div>
                <div>
                    <span>@lang('cms.Indien toevoeging bestaande lijst')</span>
                    <ul>
                        <li>@lang('cms.Volgorde kolommen hetzelfde als bestaande lijst')</li>
                    </ul>
                </div>
            </div>


            <div class="flex w-full">
                <div class="ml-auto">
                    <input id="upload-excel"
                           type="file"
                           hidden
                           accept=".xlsx,.xls"
                           x-ref="fileInputElement"
                           x-on:input="fileInput($event)"
                    >
                    <x-button.cta size="md" x-on:click="$refs.fileInputElement.click()">
                        <x-icon.checkmark />
                        <span>@lang('cms.Ik begrijp het')</span>
                    </x-button.cta>
                </div>
            </div>


        </div>
    </div>

</div>
