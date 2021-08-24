import Alpine from 'alpinejs';
window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('questionIndicator', () => ({
        showSlider: false,
        scrollStep: 100,
        totalScrollWidth: 0,
        activeQuestion: window.Livewire.find(document.querySelector('[test-take-player]').getAttribute('wire:id')).entangle('q')
    }));

    Alpine.directive('global', function (el, { expression }) {
        let f = new Function('_', '$data', '_.' + expression + ' = $data;return;');
        f(window, el._x_dataStack[0]);
    });
});