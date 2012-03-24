<!-- here comes the javascript -->

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="./_/js/jquery.jsPlumb-1.3.7-all-min.js "></script>

<!-- this is where we put our custom functions -->
<script type="text/javascript">
    jsPlumb.bind("ready", function() {        
        <?php echo $viz->generateJSFromCommunication(); ?> 
        jsPlumb.importDefaults({
            // default drag options
            DragOptions : { cursor: 'pointer', zIndex:2000 },
            // default to blue at one end and green at the other
            EndpointStyles : [{ fillStyle:'#225588' }, { fillStyle:'#558822' }],
            // blue endpoints 3 px; green endpoints 3.
            Endpoints : [ [ "Dot", {radius:3} ], [ "Dot", { radius:3 } ]],
            // the overlays to decorate each connection with.  note that the label overlay uses a function to generate the label text; in this
            // case it returns the 'labelText' member that we set on each connection in the 'init' method below.
            //ConnectionOverlays : [
            //    [ "Arrow", { location:0.9 } ],
                /*[ "Label", { 
                        location:0.1,
                        id:"label",
                        cssClass:"aLabel"
                    }]*/
            //]
        });			

        // this is the paint style for the connecting lines..
        var connectorPaintStyle = {
            lineWidth:4,
            strokeStyle:"#deea18",
            joinstyle:"round"
        },
        // .. and this is the hover style. 
        connectorHoverStyle = {
            lineWidth:4,
            strokeStyle:"#2e2aF8"
        };
        var common = {
            cssClass:"connection"
        };
        for (var row in conn)
        {
            var node = conn[row];
            var sourceStr = node['htmlid'];
            var buddiesStr = node['buddies_htmlids'];
            //document.writeln(buddiesStr);
            //$("pre#debug").append('Source : ' + sourceStr);
            if (buddiesStr.length == 0) continue;
            var buddies = buddiesStr.split(",");
            //$("pre#debug").append('Buddies : ' + buddiesStr + '<br />');
            //document.write('Source:'+source);
            for (var col in buddies)
            {            
                var buddy = buddies[col];
                //document.write('Buddy :'+ buddy);
                //$("pre#debug").append('Buddy : ' + buddy);
                jsPlumb.connect({ 
                    source: sourceStr, 
                    anchor:"BottomLeft", 
                    target: buddy, 
                    anchor:"BottomLeft",
                    endpoint:[ "Dot", { radius:3, hoverClass:"myEndpointHover" }, common ],
                    connector:[ "Flowchart", { lineWidth:3, strokeStyle: 'rgba(200,0,0,100)'}, common ]                                      
                });
            }
            //$("pre#debug").append('<br />');
            //document.writeln('');        
        }
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