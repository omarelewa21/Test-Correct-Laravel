handleFocusTextareaField = function(event, questionId)
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    return ReadspeakerTlc.rsTlcEvents.handleTextareaFocusForReadspeaker(event,questionId);
}

handleBlurTextareaField = function()
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    ReadspeakerTlc.rsTlcEvents.handleTextareaBlurForReadspeaker();
}

handleTextBoxFocusForReadspeaker = function (focusEvent,questionId)
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    ReadspeakerTlc.rsTlcEvents.handleTextBoxFocusForReadspeaker(focusEvent,questionId);
}

handleTextBoxBlurForReadspeaker = function (event,questionId)
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    ReadspeakerTlc.rsTlcEvents.handleTextBoxBlurForReadspeaker(event,questionId);
}

rsFocusSelect = function (event,selectId,questionId)
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    ReadspeakerTlc.rsTlcEvents.rsFocusSelect(event,selectId,questionId);
}

rsBlurSelect = function (event,questionId)
{
    if(typeof ReadspeakerTlc == "undefined"){
        return;
    }
    ReadspeakerTlc.rsTlcEvents.rsBlurSelect(event,questionId);
}

readspeakerLoadCore = function(){
    if(rspkr==null){
        setTimeout(readspeakerLoadCore,'1000');
        return;
    }
    if(rspkr.getLoadedMods().length>0){
        return;
    }
    rspkr.loadCore();
}