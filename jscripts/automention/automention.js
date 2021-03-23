var aut_avatar,
ment_settings;

if (parseInt(aut_avatar_set)) {
	aut_avatar = "<li><span class='am_avatar'><img src='${avatar}' onError='this.onerror=null;this.src=imagepath + \"/default_avatar.png\"';' class='am_avatar_img'></span>${text}</li>";
}
else {
	aut_avatar = "<li>${text}</li>";
}

ment_settings = {
	at: "@",
	searchKey: "text",
	displayTpl: aut_avatar,
	insertTpl: '${atwho-at}"${text}"#${uid}',
	startWithSpace: true,
	lim: aut_maxnumberitems,
	maxLen: aut_maxnamelength,
	callbacks: {
		matcher: function(flag, subtext) {
			var match, matched, regexp;
			if (parseInt(aut_spacesupp)) {
				regexp = new XRegExp('(\\s+|^)' + flag + '([\\p{L}|\\s.~\+\-\|\\p{N}]+|)$', 'gi');
			}
			else {
				regexp = new XRegExp('(\\s+|^)' + flag + '([\\p{L}|.~\+\-\|\\p{N}]+|)$', 'gi');
			}
			
			match = regexp.exec(subtext);
			if (match) {
				matched = match[2];
			}
			return matched;
		},
		remoteFilter: function(query, callback) {
			var params = {query: query};
			if (query || aut_tid) {
				if (query == '' && aut_tid) {
					params.tid = aut_tid;
				}
				$.getJSON('xmlhttp.php?action=get_users_plus', params, function(data) {
					callback(data);
				});
			} else	callback([]);
		},
		highlighter: function(li, query) {
			var regexp;
			if (!query && !parseInt(aut_avatar_set)) {
				// Custom: Add a space after the avatar span, just prior to the username.
				return li.replace("</span>", "</span> ");
			} else {
				// Simply duplicates the code from the jquery.atwho.js core highlighter function.
				regexp = new RegExp(">\\s*([^\<]*?)(" + query.replace("+", "\\+") + ")([^\<]*)\\s*<", 'ig');
				return ret = li.replace(regexp, function(str, $1, $2, $3) {
					return '> ' + $1 + '<strong>' + $2 + '</strong>' + $3 + ' <';
				});
			}
		},
		beforeReposition: function(offset) {
			if ($iframe) {
				offset.top -= $iframe.contents().find('html').scrollTop();
			}
		}
	}
}
function automentionck( local ) {
	$(local).atwho('setIframe').atwho(ment_settings);
	$(local).atwho(ment_settings);
}
$(document).ready(function() {
	if (typeof $.fn.sceditor !== 'undefined') {
		if($('#message, #signature').sceditor("instance")) {
			$iframe = $('.sceditor-container iframe');
			$($('#message, #signature').sceditor("instance").getBody()).atwho('setIframe', $iframe[0], false).atwho(ment_settings);
			$($('.sceditor-container textarea')[0]).atwho(ment_settings);
		}
		else {
			$('#message, #signature').atwho(ment_settings);
		}
		($.fn.on || $.fn.live).call($(document), 'click', '.quick_edit_button', function () {
			ed_id = $(this).attr('id');
			var pid = ed_id.replace( /[^0-9]/g, '');
			qse_area = 'quickedit_'+pid;
			setTimeout(function() {
				$iframe = $('.sceditor-container iframe');
				if ($('#'+qse_area+'').sceditor("instance")) {
					$($('#'+qse_area).sceditor("instance").getBody()).atwho('setIframe', $iframe[0], false).atwho(ment_settings);
					$('#pid_'+pid+' .sceditor-container textarea').atwho(ment_settings);
				}
				else {
					$('#'+qse_area+'').atwho(ment_settings);
				}
			},600);
		});
	}
	else {
		$('#message, #signature').atwho(ment_settings);
		($.fn.on || $.fn.live).call($(document), 'click', '.quick_edit_button', function () {
			ed_id = $(this).attr('id');
			var pid = ed_id.replace( /[^0-9]/g, '');
			qse_area = 'quickedit_'+pid;
			$('#'+qse_area+'').atwho(ment_settings);
		});
	}
	var shoutbox = '.panel > form > input[class="text"]';
	if ($(shoutbox).length) {
		$(shoutbox).atwho(ment_settings);
	}
});
