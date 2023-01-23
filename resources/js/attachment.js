import Plyr from 'plyr';


window.plyrPlayer = {
    disableElem(elem){
        // disable element if not null
        try{
            elem.setAttribute("style", "pointer-events: none;");
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
            controls: controls
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
        }

        return player
    }
}

/**
 * Takes a dom div element and makes it resizable from all corners
 * 
 * @param {object} element
 */
window.makeResizableDiv = function(element) {
    const resizers = element.querySelectorAll('.resizer')
    const minimum_size = 20;
    let original_width = 0;
    let original_height = 0;
    let original_x = 0;
    let original_y = 0;
    let original_mouse_x = 0;
    let original_mouse_y = 0;
    for (let i = 0;i < resizers.length; i++) {
        const currentResizer = resizers[i];
        currentResizer.addEventListener('mousedown', function(e) {
            e.preventDefault()
            original_width = parseFloat(getComputedStyle(element, null).getPropertyValue('width').replace('px', ''));
            original_height = parseFloat(getComputedStyle(element, null).getPropertyValue('height').replace('px', ''));
            original_x = element.getBoundingClientRect().left;
            original_y = element.getBoundingClientRect().top;
            original_mouse_x = e.pageX;
            original_mouse_y = e.pageY;
            window.addEventListener('mousemove', resize)
            window.addEventListener('mouseup', stopResize)
        })
        
        function resize(e) {
            let width, height;
            if (currentResizer.classList.contains('bottom-right')) {
                width = original_width + (e.pageX - original_mouse_x);
                height = original_height + (e.pageY - original_mouse_y)
                if (width > minimum_size) {
                    element.style.width = width + 'px'
                }
                if (height > minimum_size) {
                    element.style.height = height + 'px'
                }
            }
            else if (currentResizer.classList.contains('bottom-left')) {
                height = original_height + (e.pageY - original_mouse_y)
                width = original_width - (e.pageX - original_mouse_x)
                if (height > minimum_size) {
                    element.style.height = height + 'px'
                }
                if (width > minimum_size) {
                    element.style.width = width + 'px'
                    element.style.left = original_x + (e.pageX - original_mouse_x) + 'px'
                }
            }
            else if (currentResizer.classList.contains('top-right')) {
                width = original_width + (e.pageX - original_mouse_x)
                height = original_height - (e.pageY - original_mouse_y)
                if (width > minimum_size) {
                    element.style.width = width + 'px'
                }
                if (height > minimum_size) {
                    element.style.height = height + 'px'
                    element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                }
            }
            else {
                width = original_width - (e.pageX - original_mouse_x)
                height = original_height - (e.pageY - original_mouse_y)
                if (width > minimum_size) {
                    element.style.width = width + 'px'
                    element.style.left =  original_x + (e.pageX - original_mouse_x) + 'px'
                }
                if (height > minimum_size) {
                    element.style.height = height + 'px'
                    element.style.top = original_y + (e.pageY - original_mouse_y) + 'px'
                }
            }
        }

        function stopResize() {
            window.removeEventListener('mousemove', resize)
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

    const elementRect  = element.getBoundingClientRect();
    const windowHeight = window.innerHeight
    const windowWidth  = window.innerWidth

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
        const rightEdge = newLeft + elementRect.width;

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
    }
}