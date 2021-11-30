ReadSpeaker.q(function() {
    console.log('rs_tlc_skin initialized!');
});
window.rsConf = {
    cb: {
        ui: {
            open: function() {
                console.log('Player opened and callback fired!');
            }
        }
    },
    general: {
        usePost: true,
        skipHiddenContent:true
    },
    ui: {
        tools: {
            translation: false,
            dictionary: false
        }
    }
};