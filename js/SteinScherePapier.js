
var SteinScherePapier = function() {

	var wsckt = null;
	
	function openWebSocket() {
		console.log('openWebSocket');
		try {
			wsckt = new WebSocket('ws://localhost:8000');
			wsckt.onmessage = _receivedMessage;
			wsckt.onerror = _showSocketError;
			return wsckt;
		} catch (e) {
			jQuery("#fehler").text("Fehler: " + e).show();
		}	
	}

	function addCommandListeners() {
		jQuery('#dein_zug a')
			.click(_makeNewMove)
			.mouseover(_showNextMove)
			.mouseout(_hideNextMove);
			
		jQuery("#dein_zug, #auswertung").show();
	}

	function _showNextMove() {
		jQuery('#zug_auswahl').text(jQuery(this).data('wahl'));
	}
	function _hideNextMove() {
		jQuery('#zug_auswahl').html("&nbsp;");
	}
	
	function _showSocketError(error) {
		console.log(error);
		jQuery("#fehler").text("Fehler bei WebSocket " + error).show();
	}

	/* click-EventListener */
	function _makeNewMove(e) {
		console.log('makeNewMove');
		//console.log(e);
		e.preventDefault();
		var msg = jQuery(this).data('wahl');
		console.log("send " + msg);
		jQuery("#letzter_zug").html("Dein Zug: " + msg);
		wsckt.send(msg);
	}
	
	/* click-EventListener */
	function _sendName() {
		console.log('sendName');
		var name = jQuery("#name").val();
		console.log(name);
		var msg = jQuery(this).data('wahl');
		console.log("send " + msg);
		jQuery("#letzter_zug").html("Dein Zug: " + msg);
		wsckt.send(msg);
	}

	/* WebSocket-message-EventListener */
	function _receivedMessage(e) {
		console.log('receivedMessage');
		console.log(e);
		var msg = JSON.parse(e.data);
		var $p = _getOrCreateParagraph(msg.id);
		if (msg.login) {
			console.log("login");
			$p.find(".status").html(msg.id + ": <b>Neu eingeloggt.</b>").effect('highlight',[],1000);
		} else if (msg.logout) {
			console.log("logout");
			$p.html(msg.id + ": <b>Abgemeldet.</b>").effect('highlight',[],1000).delay(5000).hide(1000).delay(2000, function() {this.remove();});
		} else if (msg.turn) {
			console.log("turn");
			$p.find(".status").html(msg.id + ": " + msg.turn.score + " Punkte").effect('highlight',[],1000);
			$p.find(".zuege").append(" | " + msg.turn.play).effect('highlight',[],1000);
			jQuery("#letzter_zug").html("");
		}	
	}

	function _getOrCreateParagraph(id) {
		var $p = jQuery('#' + id);
		if ($p.length == 0) {
			$p = jQuery('<p>').attr('id', id).html('<span class="status"></span><span class="zuege"></span>').appendTo('#auswertung');
		}
		//console.log($p);
		return $p;
	}

	return {
		openWebSocket: openWebSocket,
		addCommandListeners: addCommandListeners
	};
	
	
}

steinScherePapier = new SteinScherePapier();
//console.log(steinScherePapier);
