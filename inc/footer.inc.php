<!-- here comes the javascript -->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="./_/js/jquery.jsPlumb-1.3.7-all-min.js "></script>

<!-- this is where we put our custom functions -->
<script type="text/javascript">
    (function($){})(window.jQuery);
    jsPlumb.bind("ready", function() {        
        <?php echo $viz->generateJSONForConnections(); ?> 
        for (var row in conn)
        {
            var node = conn[row];
            var sourceStr = node['htmlid'];
            var buddiesStr = node['buddies_htmlids'];
            //document.writeln(buddiesStr);
            
            $("pre#debug").append('Source : ' + sourceStr);
            if (buddiesStr.length == 0) continue;
            var buddies = buddiesStr.split(",");
            $("pre#debug").append('Buddies : ' + buddiesStr + '<br />');
            //document.write('Source:'+source);
            
            $("#"+sourceStr).hover(
                function () {
                    $(this).css({ opacity: 1.0});
                    for (var col in buddies)
                    {
                        var buddy = buddies[col];
                        $("#"+buddy).css({ opacity: 1.0});
                    }
                }, 
                function () {
                    $(this).css({ opacity: 0.25});
                    for (var col in buddies)
                    {
                        var buddy = buddies[col];
                        $("#"+buddy).css({ opacity: 0.25});
                    }
                }
            );
            for (var col in buddies)
            {            
                var buddy = buddies[col];
                //document.write('Buddy :'+ buddy);
                //$("pre#debug").append('Buddy : ' + buddy);
                jsPlumb.connect({ 
                    source: sourceStr, 
                    anchor: ["BottomCenter", "TopCenter"],
                    target: buddy, 
                    anchor: ["BottomCenter", "TopCenter"],
                    endpoint:[ "Dot", { cssClass:"myEndpoint", radius: 2 } ],
                    connector: [ "Flowchart", 10],     
                    endpointStyle: { fillStyle:"yellow" },
                    paintStyle: { strokeStyle:"blue", lineWidth:2 }
                });
            }
            //$("pre#debug").append('<br />');
        }
    });
    
    /* trigger when page is ready */
    $(document).ready(function (){
       $('.node').css({ opacity: 0.25});
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