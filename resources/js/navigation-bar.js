

document.addEventListener('alpine:init', () => {
    Alpine.data('navigationBar', () => ({
        hideTimeout: null,
        bottom: null,
        userMenuTimeout: null,
        supportMenuTimeout: null,
        userMenu: false,
        supportMenu: false,
        init() {
            let navBar = this.$refs.nav_bar;
            this.bottom = this.$refs.menu_bottom;
            let tiles = this.$refs.tiles;
            let scrollLeft = this.$refs.menu_scroll_left;
            let scrollRight = this.$refs.menu_scroll_right;

            var menuButtons = this.bottom.querySelectorAll('.has-items');
            menuButtons.forEach(element => {
                element.addEventListener('mouseover', (event) => {
                    menuButtons.forEach(element => {
                        tiles.querySelectorAll('.tile-group').forEach(tilegroup => { tilegroup.style.display = 'none';});
                    });
                    this.tilesBarShow();
                    var tilesGroup = tiles.querySelector('.' + event.target.dataset.menu);
                    tilesGroup.style.display = 'flex';
                    this.setPaddingForActiveTileGroupByMenuItem(event.target);
                });
            });
            this.bottom.querySelectorAll('div:not(.has-items)').forEach(element => element.addEventListener('mouseover', (event) => {
                this.tilesBarHide();
            }));
            navBar.addEventListener('mouseleave', event => {
                this.tilesBarHide();
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
        tilesBarHide() {
            this.hideTimeout = setTimeout(() => {
                tiles.style.setProperty('--top', '0px');
                tiles.style.paddingLeft = '0px';
                clearTimeout(this.hideTimeout);
            },500);
        },
        tilesBarShow() {
            clearTimeout(this.hideTimeout);
            tiles.style.paddingLeft = '0px';
            tiles.style.setProperty('--top', '98px');
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
                },1000);
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