function remoteSearch(text, cb) {
	$.getJSON('xmlhttp.php?action=get_users_plus', {query: text}, function (data) {
		cb(data);
	});
}
var tribute_ment = {
	values: function (text, cb) {
		if (text.length > 0 && text.length <= parseInt(maxnamelength)) {
			remoteSearch(text, cb);
		}
	},
	menuShowMinLength: 0,
	allowSpaces: true,
	noMatchTemplate: null,
	lookup: 'text',
	selectTemplate: function(item) {
		return '@"' + item.original.text + '"#' + item.original.uid;
	}
};
$(document).ready(function() {
	tribute_add = [];
	tribute_add.push(tribute_ment);
	var tribute_rin = new Tribute({
	  collection: tribute_add
	});
	//commented because this doesn't work with sceditor for now!
/* 	if (typeof $.fn.sceditor !== 'undefined') {
		if($('#message, #signature').sceditor("instance")) {
			tribute_rin.attach($('#message, #signature').sceditor("instance").getBody());
			tribute_rin.attach($('#message ~ div.sceditor-container textarea, #signature ~ div.sceditor-container textarea')[0]);
		}
		else {
			tribute_rin.attach('#message, #signature');
		}
		($.fn.on || $.fn.live).call($(document), 'click', '.quick_edit_button', function () {
			ed_id = $(this).attr('id');
			var pid = ed_id.replace( /[^0-9]/g, '');
			qse_area = 'quickedit_'+pid;
			setTimeout(function() {
				if ($('#'+qse_area+'').sceditor("instance")) {
					tribute_rin.attach($('#'+qse_area+'').sceditor("instance").getBody());
					tribute_rin.attach($('#'+qse_area+' ~ div.sceditor-container textarea')[0]);
				}
				else {
					tribute_rin.attach($('#'+qse_area+''));
				}
			},600);
		});
	}
	else {
		tribute_rin.attach($('#message, #signature'));
		($.fn.on || $.fn.live).call($(document), 'click', '.quick_edit_button', function () {
			ed_id = $(this).attr('id');
			var pid = ed_id.replace( /[^0-9]/g, '');
			qse_area = 'quickedit_'+pid;
			tribute_rin.attach($('#'+qse_area+''));
		});
	} */
	if (typeof $.fn.sceditor === 'undefined') {
		tribute_rin.attach($('#message, #signature'));
		($.fn.on || $.fn.live).call($(document), 'click', '.quick_edit_button', function () {
			ed_id = $(this).attr('id');
			var pid = ed_id.replace( /[^0-9]/g, '');
			qse_area = 'quickedit_'+pid;
			tribute_rin.attach($('#'+qse_area+''));
		});
	}
	var shoutbox = '.panel > form > input[class="text"]';
	if ($(shoutbox).length) {
		tribute_rin.attach($(shoutbox));
	}
	
	if (typeof CKEDITOR !== 'undefined') {
		CKEDITOR.once('currentInstance', function(){
			CKEDITOR.currentInstance.on( 'key', function( evt ) {
				if ( evt.data.domEvent.$.key  == 'Enter' && tribute_rin.isActive ) {
					return false;
				}
			});
			CKEDITOR.currentInstance.on('mode', function(editor) {
				if (editor.editor.mode == 'source') {
					tribute_rin.attach($('#'+CKEDITOR.currentInstance.name+'_2'));
				}
				else {
					tribute_rin.attach(CKEDITOR.currentInstance.document.$.body);
				}
			});
			if (CKEDITOR.instances[CKEDITOR.currentInstance.name].mode == 'source') {
				tribute_rin.attach($('#'+CKEDITOR.currentInstance.name+'_2'));
			}
			else {
				tribute_rin.attach(CKEDITOR.currentInstance.document.$.body);
			}
		});	
	}
});

