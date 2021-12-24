CKEDITOR.plugins.add('selection',{
    init: function(editor)
    {
        var pluginName = 'selection';
        editor.addCommand(
        	pluginName, new CKEDITOR.command( editor, {
	        	exec: function(editor) {
	        		var selection = editor.getSelection();
                    var text = $.trim(selection.getSelectedText());
                    var firstchar = selection.getSelectedText()[0];
                    var lastchar = selection.getSelectedText()[selection.getSelectedText().length-1];

                    if(firstchar == " ") {
                        firstchar = " [";
                    }else{
                        firstchar = "[";
                    }
                    if(lastchar == " ") {
                        lastchar = "] ";
                    }else{
                        lastchar = "]";
                    }

                    this.showPopup(editor);

	        		text = firstchar + text + lastchar;
	        		// var ranged = selection.getRanges();
	        		editor.insertText(text);
	        	},
                showPopup:function(editor){
	        	   livewire.find(document.getElementById('cms').getAttribute('wire:id')).set('showSelectionOptionsModal', true)

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
