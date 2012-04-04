<!-- here comes the javascript -->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
<script type="text/javascript" src="./_/js/jquery.jsPlumb-1.3.7-all-min.js "></script>

<!-- this is where we put our custom functions -->
<script type="text/javascript">
    (function($){})(window.jQuery);
    jsPlumb.bind("ready", function() {  
        //return;
        <?php echo $viz->generateJSONForConnections(false); ?> 
        for (var row in conn)
        {
            var node = conn[row];
            var sourceStr = node['htmlid'];
            var buddiesStr = node['buddies_htmlids'];
            //document.writeln(buddiesStr);
            
            $("pre#debug").append('Source : ' + sourceStr);
            $("pre#debug").append('| Buddies : ' + buddiesStr + '<br />');
            if (buddiesStr.length == 0) continue;
            var buddies = buddiesStr.split(",");
            
            //document.write('Source:'+source);
            /*
            
                */
            for (var col in buddies)
            {            
                var buddy = buddies[col];
                //document.write('Buddy :'+ buddy);
                //$("pre#debug").append('Buddy : ' + buddy);
                var rint = Math.round(0xffffff * Math.random());
                var color = ('#0' + rint.toString(16)).replace(/^#0([0-9a-f]{6})$/i, '#$1');
                jsPlumb.connect({ 
                    source: sourceStr, 
                    //anchor: ["BottomCenter", "TopCenter"],
                    anchor: "AutoDefault",
                    target: buddy, 
                    //anchor: ["BottomCenter", "TopCenter"],
                    anchor: "AutoDefault",
                    endpoint:[ "Dot", { cssClass:"myEndpoint", radius: 2 } ],
                    //connector: [ "StateMachine", 50],     
                    connector: [ "Flowchart", 10],     
                    //endpointStyle: { fillStyle: color },
                    endpointStyle: { fillStyle: "lightgray" },
                    //paintStyle: { strokeStyle: color, lineWidth:4 }
                    paintStyle: { strokeStyle: "lightgray", lineWidth:4 }
                });
            }
            //$("pre#debug").append('<br />');
        }
    });
      
    /* trigger when page is ready */
    
    
    $(document).ready(function (){
       
       //$(".core").css({ opacity: 0.75});       
       
       
       $.fn.hilight = function() {
            return $(this).each(function() {
                $(this).css("border-color", "red")
                //$(this).effect("highlight", {}, 1000);
            });
        };
        
        $.fn.lolight = function() {
            return $(this).each(function() {
                var col = $(this).css("background-color");
                $(this).css("border-color", col)
            });
        };
        
        <?php echo $viz->generateJQueryForHighlights(); ?>
    });
    
    
    
    
</script>
<script src="_/js/functions.js"></script>
<pre id="debug">
<?php
//echo nl2br($viz->generateJSFromCommunication());
?>
</pre>
</body>
</html>