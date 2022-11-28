CKEDITOR.plugins.add('advanced',{
    init: function(editor)
    {
        var pluginName = 'advanced';

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

	        		text = firstchar + text + lastchar;
	        		var ranged = selection.getRanges();
	        		editor.insertText(text);
	        	}
	        })
		);

        editor.ui.addButton('advanced',
        {
            label: 'Vierkante haakjes toevoegen',
            command: pluginName,
            toolbar: 'extra',
            icon: "plugins/advanced/icons/tag.png"
        });
    }
});
