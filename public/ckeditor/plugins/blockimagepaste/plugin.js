CKEDITOR.plugins.add('blockimagepaste', {

	init : function(editor) {

		function replaceImgText(html) {

      // console.log(html);

			var ret = html.replace(/<img[^>]*src=".*?"[^>]*>/gi, function(img) {
				alert("Afbeeldingen plakken is niet toegestaan.");
				return '';
			});

			var ret = ret.replace(/<a[^>]*href=".*?"[^>]*>/gi, function(link) {
				alert("Linkjes zijn niet toegestaan.");
				return '';
			});

      // console.log(ret);
      // console.log('here');

			return ret;
		}

		function chkImg() {
			// don't execute code if the editor is readOnly
			if (editor.readOnly)
				return;

			setTimeout(function() {
				editor.document.$.body.innerHTML = replaceImgText(editor.document.$.body.innerHTML);
			}, 100);
		}

		editor.on('contentDom', function() {
			// For Firefox
			editor.document.on('drop', chkImg);
			// For IE
			editor.document.getBody().on('drop', chkImg);


			var ctrlDown = false,ctrlKey = 17,cmdKey = 91,vKey = 86,cKey = 67;

	    editor.document.on('keydown',function(e) {
	      if (e.data.$.keyCode == ctrlKey || e.data.$.keyCode == cmdKey) ctrlDown = true;
				if (ctrlDown && (e.data.$.keyCode == vKey || e.data.$.keyCode == cKey)){
						chkImg();
				}
	    });

			editor.document.on('keyup',function(e) {
	      if (e.data.$.keyCode == ctrlKey || e.data.$.keyCode == cmdKey) ctrlDown = false;
	    });

		});



		editor.on('paste', function(e) {

			var html = e.data.dataValue;
			if (!html) {
				return;
			}

			e.data.dataValue = replaceImgText(html);
		});

	} // Init
});
