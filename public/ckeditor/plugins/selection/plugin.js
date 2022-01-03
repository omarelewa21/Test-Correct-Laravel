CKEDITOR.plugins.add('selection',{
    init: function(editor)
    {
        var pluginName = 'selection';
        editor.addCommand(
        	pluginName, new CKEDITOR.command( editor, {
	        	exec: function(editor) {

                    // var text = $.trim(selection.getSelectedText());
                    // var firstchar = selection.getSelectedText()[0];
                    // var lastchar = selection.getSelectedText()[selection.getSelectedText().length-1];
                    //
                    // if(firstchar == " ") {
                    //     firstchar = " [";
                    // }else{
                    //     firstchar = "[";
                    // }
                    // if(lastchar == " ") {
                    //     lastchar = "] ";
                    // }else{
                    //     lastchar = "]";
                    // }
                    window.editor = editor;

                    let lw = livewire.find(document.getElementById('cms').getAttribute('wire:id'));
                    lw.set('showSelectionOptionsModal', true)

                    // Create the event
                    var event = new CustomEvent("initwithselection");

// Dispatch/Trigger/Fire the event
                    window.dispatchEvent(event);


	        		// text = firstchar + text + lastchar;
	        		// var ranged = selection.getRanges();

	        	},


	        })
		);

        editor.ui.addButton('selection',
        {
            label: 'Vierkante haakjes toevoegen met streepjes',
            command: pluginName,
            toolbar: 'extra',
            icon: "plugins/advanced/icons/tag.png"
        });
    }
});
