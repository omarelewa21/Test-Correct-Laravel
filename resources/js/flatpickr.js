import flatpickr from "flatpickr";
import "flatpickr/dist/l10n/nl.js";

document.addEventListener("alpine:init", () => {
    Alpine.data("flatpickr", (wireModel, mode, locale, minDate, dateFormat, altFormat, enableTime) => ({
        wireModel: wireModel,
        mode: mode,
        locale: locale,
        minDate: minDate,
        picker: null,
        init() {
            this.picker = flatpickr(this.$refs.datepickr, {
                locale: this.locale,
                minDate: minDate ?? false,
                mode: this.mode,
                defaultDate: this.wireModel,
                // The displayed format is humanreadable, the used date is Y-m-d formatted;
                altInput: true,
                altFormat,
                dateFormat,
                enableTime,
                onChange: (date, dateString) => {
                    this.wireModel = this.value = this.mode === "range" ? dateString.split(" t/m ") : dateString; //split t/m or to
                },
                onOpen: () => {
                    this.$root.parentElement.querySelector("label")?.classList.add("text-primary", "bold");
                },
                onClose: () => {
                    this.$root.parentElement.querySelector("label")?.classList.remove("text-primary", "bold");
                }
            });
        },
        clearPicker() {
            this.picker.setDate("", false);
        }
    }));

});