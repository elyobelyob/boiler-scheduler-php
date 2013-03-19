function checkStatus() {
	$.getJSON("/api/heating/status/", function(json) {
		if(json.Result == "OK") {
			/* updateImages(json); */
		} else {
			alert(json.Message);
		}
	});
	t = setTimeout('checkStatus()', 30000);
}
$(document).ready(checkStatus());

function setupDialog(dialogId, postName, timeId, tempId, delayId, buttonId) {
    $( dialogId ).dialog({
        autoOpen: false,
        height: 200,
        width: 250,
        modal: true,
        buttons: {
          "Toggle": function() {
        	  $.post("/api/override/boost/toggle/" + postName +"/time/" + $(timeId).val() +"/temp/" + $(tempId).val() +"/delay/" + $(delayId).val(), function(json) {
      			if (json.Result == "OK") {
/*       				updateImages(json); */
      			} else {
      				$dialog.html("Oops something went wrong");
      			}
      		}, "json");
              $( this ).dialog( "close" );
          },
          "Cancel": function() {
            $( this ).dialog( "close" );
          }
        },
        close: function() {
          $(timeId).val( "60" );
        }
      });
	
	$(buttonId).click(function() {
		if ($(buttonId).attr('src') == '/images/on.png') {
			$(timeId).prop('disabled', true);
		} else if ($(buttonId).attr('src') == '/images/off.png') {
			$(timeId).prop('disabled', false);
		}
		
		$( dialogId ).dialog( "open" );
	});
}

$(document).ready(function() {
	setupDialog("#heating-boost-dialog", "heating", "#heatTime", "#heatTemp", "#heatDelay","#heatingBoost");
	setupDialog("#water-boost-dialog", "water", "#waterTime", "#waterBoost");
});