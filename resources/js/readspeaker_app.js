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