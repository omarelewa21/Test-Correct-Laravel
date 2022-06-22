import flatpickr from "flatpickr";
import "flatpickr/dist/l10n/nl.js";

document.addEventListener('alpine:init', () => {
    Alpine.data('flatpickr', (wireModel, mode, locale, minDate) => ({
        wireModel: wireModel,
        mode: mode,
        locale: locale,
        minDate: minDate,
        init() {

            // if(this.mode == 'range'){
            //     this.value = ['{{$defaultDate}}', '{{$defaultDateTo}}'];
            // } else {
            //     this.value = this.wireModel;
            // }
            let picker = flatpickr(this.$refs.datepickr, {
                locale: this.locale,
                minDate: minDate == 'today' ? 'today' : false,
                mode: this.mode,
                defaultDate: this.wireModel,
                dateFormat: "d-m-Y",
                onChange: (date, dateString) => {
                   this.wireModel = this.value = this.mode == 'range' ? dateString.split(' t/m ') : dateString; //split t/m or to
                }
            })
        }
    }));

});