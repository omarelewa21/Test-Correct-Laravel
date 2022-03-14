import Plyr from 'plyr';


window.plyrPlayer = {
    disableElem(elem, replace=true){
        // Takes elem and disable it by css and byChoice replacing it to remove all event listeners
        if(replace){
            let elClone = elem.cloneNode(true);
            elem.parentNode.replaceChild(elClone, elem);
            elClone.setAttribute("style", "pointer-events: none;");
        }else{
            elem.setAttribute("style", "pointer-events: none;");
        }
    },

    noPause(playElem){
        // Takes play button elem and Disable it after clicked play first time
        playElem.addEventListener('click', function(){
            setTimeout(()=>{
                plyrPlayer.disableElem(this);
            }, 100)
        });
    },

    render(elem,
        constraints={},
        controls=['play', 'progress', 'current-time', 'mute', 'volume'],
        disableProgressElem=false
        )
    {
        var player = new Plyr(elem, {
            controls: controls
        });

        // let controlsElem = player.elements.controls;            // Get the play button
        let progressElem = player.elements.progress;            // Get the progress bar element

        // controlsElem.setAttribute("wire:ignore", "");

        if(disableProgressElem){
            this.disableElem(progressElem, false);
        }

        // Todo display the play-pause button and apply the constraints on it 

        // let playElem     = player.elements.controls;            // get The controlls element
        // let controlsElem = player.elements.buttons.play[0];     // Get the play button
        // let progressElem = player.elements.progress;            // Get the progress bar element

        // if(! constraints.pausable){
        //     this.noPause(playElem);
        // }

        // if(! constraints.pausable || constraints.playableOnce || constraints.withTimeout){
        //     this.disableElem(progressElem, false);
        // }
        return player
    },
}
