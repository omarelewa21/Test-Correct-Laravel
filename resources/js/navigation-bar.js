document.addEventListener('alpine:init', () => {
    Alpine.data('navigationBar', () => ({
        hideTimeout: null,
        bottom: null,
        userMenuTimeout: null,
        supportMenuTimeout: null,
        userMenu: false,
        supportMenu: false,
        menuButtons: null,
        menuButtonsWithoutItems: null,
        activeMenuItem: null,
        init() {
            let navBar = this.$refs.nav_bar;
            this.bottom = this.$refs.menu_bottom;
            let tiles = this.$refs.tiles;
            let scrollLeft = this.$refs.menu_scroll_left;
            let scrollRight = this.$refs.menu_scroll_right;

            this.menuButtonsWithItems = this.bottom.querySelectorAll('.has-items');
            this.menuButtonsWithoutItems = this.bottom.querySelectorAll('div:not(.has-items)');

            if (this.$wire.activeRoute.sub !== '') {
                let activeTileItem = navBar.querySelector('.' + this.$wire.activeRoute.sub);
                activeTileItem.classList.add('tile-active');
            }
            if (this.$wire.activeRoute.main !== ''){
                this.activeMenuItem = this.bottom.querySelector('[data-menu="' + this.$wire.activeRoute.main + '"]');
                this.activeMenuItem.classList.add('button-active');
            }
            this.resetActiveState();

            this.menuButtonsWithItems.forEach(element => {
                element.addEventListener('mouseover', (event) => {
                    if(this.activeMenuItem) {
                        this.activeMenuItem.classList.remove('button-active');
                    }
                    this.tileItemsHide();
                    this.tilesBarShow();
                    var tilesGroup = tiles.querySelector('.' + event.target.dataset.menu);
                    tilesGroup.style.display = 'flex';
                    this.setPaddingForActiveTileGroupByMenuItem(event.target);
                });
            });
            this.bottom.querySelectorAll('div:not(.has-items)').forEach(element => element.addEventListener('mouseover', (event) => {
                this.tilesBarHide(0, false);
                if(this.activeMenuItem){
                    this.activeMenuItem.classList.remove('button-active');
                }
            }));
            navBar.addEventListener('mouseleave', event => {
                this.tilesBarHide(500);
            });
            scrollLeft.addEventListener('click', event => {
                this.menuBottomScrollLeft();
            });
            scrollRight.addEventListener('click', event => {
                this.menuBottomScrollRight();
            });
            this.$refs.user_button.addEventListener('click', event => {
                this.userMenuShow();
            });
            this.$refs.support_button.addEventListener('click', event => {
                this.supportMenuShow();
            });

        },
        tileItemsHide(){
            this.menuButtonsWithItems.forEach(element => {
                tiles.querySelectorAll('.tile-group').forEach(tilegroup => { tilegroup.style.display = 'none';});
            });
        },
        tilesBarHide(timeout = 1, reset = true) {
            this.hideTimeout = setTimeout(() => {
                this.tileItemsHide();
                tiles.style.setProperty('--top', '0px');
                tiles.style.paddingLeft = '0px';
                clearTimeout(this.hideTimeout);
                this.$dispatch('tiles-hidden');
                if(reset){
                    this.resetActiveState();
                    this.$dispatch('tiles-shown');
                }
            },timeout);
            // alert(this.$wire.activeRoute.main == '');
        },
        resetActiveState() {
            if (this.$wire.activeRoute.sub !== ''){
                tiles.style.setProperty('--top', '100px');

                var activeTile = tiles.querySelector('.' + this.$wire.activeRoute.main);
                activeTile.style.display = "flex";

                //menu item
                this.setPaddingForActiveTileGroupByMenuItem(this.activeMenuItem);
            }
            if (this.activeMenuItem){
                this.activeMenuItem.classList.add('button-active');
            }
        },
        tilesBarShow() {
            clearTimeout(this.hideTimeout);
            tiles.style.paddingLeft = '0px';
            tiles.style.setProperty('--top', '100px');
            this.$dispatch('tiles-shown');
        },
        userMenuShow() {
            clearTimeout(this.userMenuTimeout);
            if(this.userMenu === false){
                this.userMenu = true;
                this.userMenuTimeout = setTimeout(() => {
                    this.userMenu = false;
                },5000);
            } else {
                this.userMenu = false;
            }
        },
        supportMenuShow() {
            clearTimeout(this.supportMenuTimeout);
            if(this.supportMenu === false){
                this.supportMenu = true;
                this.supportMenuTimeout = setTimeout(() => {
                    this.supportMenu = false;
                },5000);
            } else {
                this.supportMenu = false;
            }
        },
        setPaddingForActiveTileGroupByMenuItem (menuItem) {
            var menuItem = menuItem;
            var tileGroup = tiles.querySelector('.' + menuItem.dataset.menu);
            var minimalPadding = this.bottom.querySelector('.menu-item:nth-child(2)').offsetLeft;
            var maximalPadding = tiles.offsetWidth - tileGroup.offsetWidth;
            var calculatedPadding =  menuItem.getBoundingClientRect().right - (menuItem.offsetWidth / 2) - (tileGroup.offsetWidth / 2);

            if (calculatedPadding < minimalPadding) {
                return tiles.style.paddingLeft = minimalPadding + 'px';
            }
            if (calculatedPadding > maximalPadding) {
                return tiles.style.paddingLeft = maximalPadding + 'px';
            }
            tiles.style.paddingLeft = calculatedPadding + 'px';
        },
        menuBottomScrollRight() {
            this.bottom.scrollBy({ left: 150, top: 0, behavior: 'smooth'});
        },
        menuBottomScrollLeft() {
            this.bottom.scrollBy({ left: -150, top: 0, behavior: 'smooth'});
        },
    }));
});