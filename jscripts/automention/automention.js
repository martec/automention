var aut_avatar,
ment_settings,
old_data = [],
old_query = '',
similar_query,
first_try = true;

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
	limit: aut_maxnumberitems,
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
			if (match && match[2].length <= aut_max_length) {
				matched = match[2];
			}
			return matched;
		},
		afterMatchFailed: function(at, el) {
			return false;
		},
		remoteFilter: function(query, callback) {
			var params = {query: query},
			similar_query = query.trim().includes(old_query.trim(), 0);
			if ((query.length > 1 && first_try) || (query.length > 1 && !similar_query) || (query.length > 1 && similar_query && old_data.length > 0) || (aut_tid && query == '' && parseInt(aut_thread_part))) {
				if (query == '' && aut_tid) {
					params.tid = aut_tid;
				}
				$.getJSON('xmlhttp.php?action=get_users_plus', params, function(data) {
					old_query = query;
					old_data = data;
					first_try = false;
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
			if (typeof $iframe !== 'undefined') {
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
		if (typeof $('#message, #signature').sceditor("instance").getBody === 'function') {
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
