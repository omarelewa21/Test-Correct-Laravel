import Plyr from 'plyr';
import Alpine from "alpinejs";


window.plyrPlayer = {
    disableElem(elem){
        // disable element if not null
        try{
            elem.setAttribute("style", "pointer-events: none;");
            elem.setAttribute('disabled', true);
        }
        catch(e){}
    },

    noPause(player){
        // Takes player and disable play button after clicking first time
        let playElem = player.elements.buttons.play[0];
        playElem.addEventListener('click', function(){
            setTimeout(()=>{
                plyrPlayer.disableElem(this);
            }, 100)
        });
    },

    disableTimeline(player){
        // Takes player and disable timeline
        let timeline = player.elements.progress;
        this.disableElem(timeline);
    },

    Onplaying(player, mode, wire, attachmentUuid){
        // Send requests for playing and pause based on mode
        if(mode === "notPreview"){
            player.on('playing', () => {
                wire.registerPlayStart();
            });

            player.on('pause', () => {
                wire.audioStoreCurrentTime(attachmentUuid, player.currentTime);
            });
        }else{
            player.on('playing', () => {
                wire.set('pressedPlay', true);
            });

            player.on('pause', () => {
                wire.audioStoreCurrentTime(attachmentUuid, player.currentTime);
            });
        }
    },

    parseConstraints(constraints){
        // Parsing constraints json variable and returns an object
        let data = JSON.parse(constraints);
        return {
            pausable:   typeof data.pausable  !== 'undefined' && data.pausable === "1",
            play_once:  typeof data.play_once !== 'undefined' && data.play_once === "1",
            hasTimeout: typeof data.timeout   !== 'undefined' && data.timeout  !== ""
        }
    },

    applyConstraints(player, constraints, wire, mode, attachmentUuid){
        if(!constraints.pausable){
            this.noPause(player);
        }

        if(constraints.hasTimeout || constraints.play_once){
            if(mode === "notPreview"){
                player.on('ended', () => {
                    wire.registerEndOfAudio(player.currentTime, player.duration);
                    if(constraints.play_once){
                        wire.audioIsPlayedOnce();
                    }
                    wire.audioStoreCurrentTime(attachmentUuid, 0);
                    wire.closeAttachmentModal(true);
                });
            }else{
                player.on('ended', () => {
                    wire.audioStoreCurrentTime(attachmentUuid, 0);
                    wire.closeAttachmentModal(true);
                })
            }
        }else{
            player.on('ended', () => {
                wire.audioStoreCurrentTime(attachmentUuid, 0);
                wire.closeAttachmentModal(true);
            })
        }

        if(!constraints.pausable || constraints.hasTimeout || constraints.play_once){
            this.disableTimeline(player);
        }
    },

    render(elem, wire, attachmentUuid, constraints, audioCanBePlayedAgain=true, mode="notPreview", controls=['play', 'progress', 'current-time', 'mute', 'volume'])
    {
        let player = new Plyr(elem, {
            controls: controls,
            iconUrl: '/svg/plyr.svg'
        });

        this.Onplaying(player, mode, wire, attachmentUuid);

        if(constraints.length !== 0){
            this.applyConstraints(player, this.parseConstraints(constraints), wire, mode, attachmentUuid);
        }else{
            this.noPause(player);
            this.disableTimeline(player);
        }

        if(!audioCanBePlayedAgain){
            this.disableElem(player.elements.buttons.play[0]);
            this.disableElem(player.elements.progress.getElementsByTagName('input')[0]);
        }

        return player
    },

    renderWithoutConstraints(elem) {
        let controls = ['play', 'progress', 'current-time', 'mute', 'volume'];
        let player = new Plyr(elem, {
            controls: controls,
            iconUrl: '/svg/plyr.svg'
        });
        return player;
    }
}

/**
 * Takes a dom div element and makes it resizable from all corners
 *
 * @param {object} element
 * @param {string} attachmentType
 */
window.makeAttachmentResizable = function(element, attachmentType='') {
    if (attachmentType === 'audio') return;
    const resizers = element.querySelectorAll('.resizer')
    const iframe = element.querySelector('.resizers iframe');
    const minimum_size = 167;
    let maximum_x = 1000;
    let maximum_y = 1000;
    let iframeTimeout = 0;
    let original_width = 0;
    let original_height = 0;
    let original_x = 0;
    let original_y = 0;
    let original_mouse_x = 0;
    let original_mouse_y = 0;
    let width, height;
    let img;             // Specific for image attachments

    for (let i = 0;i < resizers.length; i++) {
        const currentResizer = resizers[i];
        currentResizer.addEventListener('mousedown', resizeMouseDown);
        currentResizer.addEventListener('ontouchstart', resizeMouseDown);

        function resizeMouseDown(e) {
            e.preventDefault()
            original_width = parseFloat(getComputedStyle(element, null).getPropertyValue('width').replace('px', ''));
            original_height = parseFloat(getComputedStyle(element, null).getPropertyValue('height').replace('px', ''));
            original_x = element.getBoundingClientRect().left;
            original_y = element.getBoundingClientRect().top;
            original_mouse_x = e.pageX;
            original_mouse_y = e.pageY;
            maximum_x = document.body.offsetWidth;
            maximum_y = document.body.offsetHeight;

            if(attachmentType === 'image') {
                setImageProperties(element);
            }

            window.addEventListener('mousemove', resize)
            window.addEventListener('ontouchmove', resize)
            window.addEventListener('mouseup', stopResize)
            window.addEventListener('ontouchend', stopResize)


            /*************************** Main *****************************/
            function resize(e) {
                if(attachmentType === 'pdf' || attachmentType === 'video') {
                    iframeTimeout = temporarilyDisablePointerEvents(iframe, iframeTimeout);
                }

                if (currentResizer.classList.contains('bottom-right')) resizeBottomRight(e);
                else if (currentResizer.classList.contains('bottom-left')) resizeBottomLeft(e);
                else if (currentResizer.classList.contains('top-right')) resizeTopRight(e);
                else resizeTopLeft(e);
            }
            function stopResize() {
                if(attachmentType === 'pdf' || attachmentType === 'video'){
                    resetTemporarilyDisabledPointerEvents(iframe, iframeTimeout);
                }
                if(attachmentType === 'image') {
                    scaleModalAndImageToRatio();
                }
                window.removeEventListener('mousemove', resize);
                window.removeEventListener('touchmove', resize);
                window.removeEventListener('mouseup', stopResize)
                window.removeEventListener('ontouchend', stopResize)
            }


            /*************************** Helpers *****************************/
            function resizeBottomRight(e) {
                width = original_width + (e.pageX - original_mouse_x);
                height = original_height + (e.pageY - original_mouse_y)

                if (width > minimum_size && e.pageX <= maximum_x) {
                    element.style.width = width + 'px'
                }
                if (height > minimum_size && e.pageY <= maximum_y) {
                    element.style.height = height + 'px'
                }
            }
            function resizeBottomLeft(e) {
                width = original_width - (e.pageX - original_mouse_x)
                height = original_height + (e.pageY - original_mouse_y)
                if (width > minimum_size && e.pageX > 0) {
                    element.style.width = width + 'px'
                    element.style.left = original_x + (e.pageX - original_mouse_x) + 'px'
                }
                if (height > minimum_size && e.pageY <= maximum_y) {
                    element.style.height = height + 'px'
                }
            }
            function resizeTopRight(e) {
                width = original_width + (e.pageX - original_mouse_x)
                height = original_height - (e.pageY - original_mouse_y)
                if (width > minimum_size && e.pageX <= maximum_x) {
                    element.style.width = width + 'px'
                }
                if (height > minimum_size && e.clientY > 0) {
                    element.style.height = height + 'px'
                    element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                }
            }
            function resizeTopLeft(e) {
                width = original_width - (e.pageX - original_mouse_x)
                height = original_height - (e.pageY - original_mouse_y)
                if (width > minimum_size && e.pageX > 0) {
                    element.style.width = width + 'px'
                    element.style.left = original_x + (e.pageX - original_mouse_x) + 'px'
                }
                if (height > minimum_size && e.clientY > 0) {
                    element.style.height = height + 'px'
                    element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                }
            }
            function setImageProperties(){
                if(typeof img === 'undefined'){
                    img = element.querySelector('img');
                    img.closest('.image-max-height').style.maxHeight = 'initial';   // Remove max height from parent dev to allow img expands if bigger than the parent when resized
                }
                img.style.opacity = '0';
            }
            function scaleModalAndImageToRatio() {
                /*  Rule of thumb: The image cannot become larger than the 'pulled' size of the parent. */
                const current = element.getBoundingClientRect();
                const [newWidth, newHeight] = calculateMaxAspectRatioFit(current.width , current.height, img.naturalWidth , img.naturalHeight);

                img.style.width = newWidth + 'px';
                img.style.height = newHeight + 'px';

                element.style.width = 'auto';
                element.style.height = 'auto';
                img.style.opacity = '1';
            }
        }
    }
}

/**
 * Drag of attachment
 *
 * @param {object} element
 */
window.dragElement = function (element) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    let newTop, newLeft;

    const windowHeight = window.innerHeight
    const windowWidth  = window.innerWidth

    const iframe = element.querySelector('.resizers iframe');
    let iframeTimeout = 0;

    if (document.getElementById(element.id + "drag")) {
        // if present, the header is where you move the DIV from:
        document.getElementById(element.id + "drag").onmousedown = dragMouseDown;
        document.getElementById(element.id + "drag").ontouchstart = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        element.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        e = e || window.event;
        // get the mouse cursor position at startup:
        if (e.type === 'touchstart') {
            pos3 = e.touches[0].clientX;
            pos4 = e.touches[0].clientY;
        } else {
            pos3 = e.clientX;
            pos4 = e.clientY;
        }
        document.onmouseup = closeDragElement;
        document.ontouchend = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
        document.ontouchmove = elementDrag;
    }

    function elementDrag(e) {
        iframeTimeout = temporarilyDisablePointerEvents(iframe, iframeTimeout);

        e = e || window.event;

        // calculate the new cursor position:
        if (e.type === 'touchmove') {
            pos1 = pos3 - e.touches[0].clientX;
            pos2 = pos4 - e.touches[0].clientY;
            pos3 = e.touches[0].clientX;
            pos4 = e.touches[0].clientY;
        } else {
            pos1 = pos3 - e.clientX;
            pos2 = pos4 - e.clientY;
            pos3 = e.clientX;
            pos4 = e.clientY;
        }
        // set the element's new position:
        newTop  = (element.offsetTop - pos2);
        newLeft = (element.offsetLeft - pos1);

        element.style.top  = newTop  + "px";
        element.style.left = newLeft + "px";

    }

    function closeDragElement(e) {
        const rightEdge = newLeft + element.getBoundingClientRect().width;

        if(newTop  < 0){newTop  = 10}                                   // Check if the top edge is within window height boundaries
        else if(newTop  > windowHeight-50){newTop = windowHeight-50}

        if(rightEdge < 150){newLeft = 0}                              // Check if the right edge is within window width boundaries
        else if(rightEdge > windowWidth-10){newLeft = 0}

        element.style.top = newTop + 'px';
        element.style.left = newLeft + 'px';

        document.onmouseup = null;
        document.ontouchend = null;
        document.onmousemove = null;
        document.ontouchmove = null;

        resetTemporarilyDisabledPointerEvents(iframe, iframeTimeout);
    }
}

let temporarilyDisablePointerEvents = (element, timeout, milliseconds = 500) => {
    if(!element) {
        return false;
    }

    element.style.pointerEvents = 'none';
    if(timeout){
        clearTimeout(timeout);
    }
    return setTimeout(() => {
        resetTemporarilyDisabledPointerEvents(element, timeout);
    }, milliseconds);
}

let resetTemporarilyDisabledPointerEvents = (element, timeout) => {
    if(!element) {
        return false;
    }

    if(timeout){
        clearTimeout(timeout);
    }
    element.style.pointerEvents = 'auto';
}

document.addEventListener("alpine:init", () => {
    Alpine.data("attachmentModal", (attachmentType) => ({
        maxHeight: 0,
        maxWidth: 0,
        image: null,
        imageWidth: 0,
        imageHeight: 0,
        attachmentType,
        init() {
            if (attachmentType === "image") {
                this.maxHeight = window.innerHeight * 0.8;
                this.maxWidth = window.innerWidth * 0.9;
                this.image = this.$root.querySelector("img");
                this.imageLoaded();
            }
            dragElement(this.$el);
            makeAttachmentResizable(this.$el, attachmentType);
        },
        imageLoaded() {
            if (attachmentType !== "image" || !(this.image.naturalWidth > 0)) return;

            this.$root.style.width = "auto";
            this.$root.style.height = "auto";

            const [maxWidth, maxHeight] = calculateMaxAspectRatioFit(
                this.maxWidth,
                this.maxHeight,
                this.image.naturalWidth,
                this.image.naturalHeight
            );
            this.imageWidth = this.getImageWidth(maxWidth);
            this.imageHeight = this.getImageHeight(maxHeight);
        },
        getImageWidth(maxWidth) {
            return (this.image.naturalWidth > maxWidth ? maxWidth : this.image.naturalWidth) + 'px'
        },
        getImageHeight(maxHeight) {
            return (this.image.naturalHeight > maxHeight ? maxHeight : this.image.naturalHeight) + "px";
        },

    }));
});

let calculateMaxAspectRatioFit = (maxWidth, maxHeight, imageWidth, imageHeight) => {
    const scale = Math.min(maxWidth / imageWidth, maxHeight / imageHeight);

    let newMaxWidth = Math.floor(imageWidth * scale),
        newMaxHeight = Math.floor(imageHeight * scale);

    return [newMaxWidth, newMaxHeight];
};