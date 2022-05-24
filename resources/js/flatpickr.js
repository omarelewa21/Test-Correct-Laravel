import flatpickr from "flatpickr";
import "flatpickr/dist/l10n/nl.js";

document.addEventListener('alpine:init', () => {
    Alpine.data('flatpickr', (wireModel, mode, locale) => ({
        wireModel: wireModel,
        mode: mode,
        locale: locale,
        init() {

            // if(this.mode == 'range'){
            //     this.value = ['{{$defaultDate}}', '{{$defaultDateTo}}'];
            // } else {
            //     this.value = this.wireModel;
            // }
console.dir(this.$refs.datepickr);
            let picker = flatpickr(this.$refs.datepickr, {
                locale: this.locale,
                mode: this.mode,
                defaultDate: this.wireModel,
                onChange: (date, dateString) => {
                    console.log('change');
                   this.wireModel = this.value = this.mode == 'range' ? dateString.split(' t/m ') : dateString; //split t/m or to
                }
            })
        }
    }));

});