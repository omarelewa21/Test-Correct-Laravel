import Sortable from 'sortablejs';

window.sortable = Sortable;
if (typeof window.Livewire === 'undefined') {
    throw 'Livewire Sortable.js Plugin: window.Livewire is undefined. Make sure @livewireScripts is placed above this script include';
}

window.Livewire.directive('sortable', (el, directive, component) => {
    // Only fire this handler on the "root" directive.
    if (directive.modifiers.length > 0) {
        return;
    }

    el.livewire_sortable = Sortable.create(el, {
        draggable: '[wire\\:sortable\\.item]',
        handle: el.querySelector('[wire\\:sortable\\.handle]') ? '[wire\\:sortable\\.handle]' : null,
        sort: true,
        dataIdAttr: 'wire:sortable.item',
        group: {
            name: el.getAttribute('wire:sortable'),
            pull: false,
            put: false,

        },
        forceFallback: el.closest('.sortable-drawer') ? true : false,
        store: {
            set: function (sortable) {
                let items = sortable.toArray().map((value, index) => {
                    return {
                        order: index + 1,
                        value: value,
                    };
                });

                component.call(directive.method, items);
            },
        },
        onStart: (evt) => {
            if(evt.target.closest('.drawer')){
                const groups = evt.target.closest('.drawer').querySelectorAll('.draggable-group');
                for (const group of groups) {
                    if(group != evt.target) {
                        group.classList.add('sortable-nogo');
                    }
                }
            }
        },
        onEnd: (evt) => {
            if(evt.target.closest('.drawer')){
                const groups = evt.target.closest('.drawer').querySelectorAll('.draggable-group');

                for (const group of groups) {
                    group.classList.remove('sortable-nogo');
                }
            }
        }
    });
});

window.Livewire.directive('sortable-group', (el, directive, component) => {
    // Only fire this handler on the "root" group directive.
    if (! directive.modifiers.includes('item-group')) {
        return;
    }

    el.livewire_sortable = Sortable.create(el, {
        draggable: '[wire\\:sortable-group\\.item]',
        handle: el.querySelector('[wire\\:sortable-group\\.handle]') ? '[wire\\:sortable-group\\.handle]' : null,
        sort: true,
        dataIdAttr: 'wire:sortable-group.item',
        group: {
            name: el.closest('[wire\\:sortable-group]').getAttribute('wire:sortable-group'),
            pull: true,
            put: true,
        },
        forceFallback: true,
        onSort: () => {
            let masterEl = el.closest('[wire\\:sortable-group]');

            let groups = Array.from(masterEl.querySelectorAll('[wire\\:sortable-group\\.item-group]')).map((el, index) => {
                return {
                    order: index + 1,
                    value: el.getAttribute('wire:sortable-group.item-group'),
                    items:  el.livewire_sortable.toArray().map((value, index) => {
                        return {
                            order: index + 1,
                            value: value
                        };
                    }),
                };
            });

            component.call(masterEl.getAttribute('wire:sortable-group'), groups);
        },
        onStart: (evt) => {
            if(evt.target.closest('.drawer')){

                const items = evt.target.closest('.drawer').querySelectorAll('.drag-item');

                for (const item of items) {
                    item.classList.add('sortable-nogo');
                }

                const okItems = evt.target.closest('.draggable-group').querySelectorAll('.drag-item');

                evt.target.closest('.draggable-group').classList.remove('sortable-nogo');

                for (const item of okItems) {console.log('remove');
                    item.classList.remove('sortable-nogo');
                }
            }
        },
        onEnd: (evt) => {
            if(evt.target.closest('.drawer')){
                const items = evt.target.closest('.drawer').querySelectorAll('.drag-item');

                for (const item of items) {
                    item.classList.remove('sortable-nogo');
                }

            }
        }
    });
});