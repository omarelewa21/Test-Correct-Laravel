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
