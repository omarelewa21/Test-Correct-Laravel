ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
});
window.rsConf = {
    cb: {
        ui: {
            open: function() {
                console.log('Player opened and callback fired!');
            },
            close: function() {
                console.log('Player closed and callback fired!');
                var oldEl = document.getElementById('there_can_only_be_one');
                if(oldEl){
                    oldEl.remove();
                    document.getElementsByClassName('cke_wysiwyg_frame')[0].classList.remove('hidden');
                }
            }
        }
    },
    general: {
        usePost: true,
        skipHiddenContent:true
    }
};