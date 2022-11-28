// MarkO: Hiding UI elements in viewer.html so our clients get limited options

function hideGenericOptions() {
    document.getElementById("download").setAttribute("hidden", true);
    document.getElementById("openFile").setAttribute("hidden", true);
    document.getElementById("secondaryToolbarToggle").setAttribute("hidden", true);
    document.getElementById("viewBookmark").setAttribute("hidden", true);
    document.getElementById("viewAttachments").setAttribute("hidden", true);
    document.getElementById("sidebarToggle").setAttribute("hidden", true);
    document.getElementById("sidebarContainer").setAttribute("hidden", true);
}

function hideStudentOptions() {
    hideGenericOptions();
    document.getElementById("print").setAttribute("hidden", true);
}


hideOptions = window.studentButtons ? hideStudentOptions : hideGenericOptions;

if(window.attachEvent) {
    window.attachEvent('onload', hideOptions);
} else {
    if(window.onload) {
        var curronload = window.onload;
        window.onload = function(event) {
            curronload(event);
            hideOptions();
        };
    } else {
        window.onload = hideOptions;
    }
}