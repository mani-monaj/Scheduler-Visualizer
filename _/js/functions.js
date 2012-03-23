// remap jQuery to $
(function($){})(window.jQuery);


/* trigger when page is ready */
$(document).ready(function (){

	// your functions go here
});

jsPlumb.bind("ready", function() {
// your jsPlumb related init code goes here
//var e0 = jsPlumb.addEndpoint("node0"),
//	e1 = jsPlumb.addEndpoint("node1");

	jsPlumb.connect({ source:"node0", anchor:"BottomLeft", target:"node1", anchor:"TopRight" });
});

/* optional triggers

$(window).load(function() {
	
});

$(window).resize(function() {
	
});

*/