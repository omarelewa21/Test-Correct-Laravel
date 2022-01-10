ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
    rspkr.ui.Tools.ClickListen.activate();
});
window.rsConf = {
    general: {
        usePost: true,
        skipHiddenContent:true
    },
    ui: {
        scrollcontrols: {
            vertical : 'bottom',
            horizontal: 'left'
        }
    },
    cb: {
        ui: {
            beforeclose: function(){
                var focusedElements = document.getElementsByClassName('rs-cl-tabbable');
                if(typeof focusedElements.length == "undefined" || focusedElements.length==0){
                    return;
                }
                focusedElements[0].classList.remove('rs-cl-tabbable');
            }
        }
    }
};

function handleFocusForReadspeaker(){
    //if clickListen is activated you cannot type an L in a textfield
    rspkr.ui.Tools.ClickListen.deactivate();
}
function handleBlurForReadspeaker(){
    rspkr.ui.Tools.ClickListen.activate();
}