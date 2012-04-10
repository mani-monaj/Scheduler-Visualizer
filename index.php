<?php require_once("./inc/header.inc.php");?>
<?php
            require_once("./inc/visualizer.inc.php");
            
            $set = $_GET['set'];            
            if (empty($set)) die("Can not run without solution set!");
            
            //$_n = (int) $_GET['nodes'];
            //if ($_n == 0) $nodes = 100;
            
            $_cpn = (int) $_GET['corespernode'];
            if ($_cpn == 0) $_cpn = 2;
            
            $viz = new CVisualizer();
            $ready = $viz->init($_cpn, "./data/$set/names.csv", "./data/$set/d.csv", "./data/$set/c.csv", "./data/$set/x.csv", "./data/$set/coeff.csv");
            ?>
<div class="wrapper">
	<div id="infobar">
            <?php if ($ready) : ?>
            <?php
                $stat = $viz->getStats();
                $obj = $viz->getObjectiveFunctionValue();
            ?>
            <table width="100%" border="0" cellpadding="2" cellspacing="2">
                <tr class="odd"">
                    <td>#Nodes</td>
                    <td>#Cores</td>
                    <td>#Processes</td>
                    <td>Wp</td>
                    <td>Wc</td>
                    <td>Wd</td>
                    <td>#Contentions</td>
                    <td>#CoScheduledPairs</td>
                    <td>#PowerOnNodes</td>
                    <td>Obj Function (A)</td>
                    <td>Obj Function (J)</td>
                </tr>
                <tr class="even">
                    <td><?=$stat["numNodes"]?></td>
                    <td><?=$stat["numCores"]?></td>
                    <td><?=$stat["numProcesses"]?></td>
                    <td><?=$stat["wp"]?></td>
                    <td><?=$stat["wc"]?></td>
                    <td><?=$stat["wd"]?></td>
                    <td><?=$stat["numContendedNodes"]?></td>
                    <td><?=$stat["numCoScheduledBuddies"]?></td>
                    <td><?=$stat["numPoweredOnNodes"]?></td>
                    <td><?=$obj["arash"]?></td>
                    <td><?=$obj["jess"]?></td>
                </tr>
            </table>
            <?php endif; ?>
        </div>
        <br clear="all" style="clear: both" />
        <article>
		
            <?php
            //if (!$viz->init(4, 2, "./data/dummy-names.csv", "./data/dummy-devils.csv", "./data/dummy-c.csv", "./data/dummy-x.csv"))
            //if (!$viz->init(100, 2, "", "./data/arash-d.csv", "./data/arash-c.csv", "./data/arash-x.csv"))
            if (!$ready)
            {
                echo $viz->getError();
            }
            else
            {
                $viz->visualize();
                
                //print_r($viz->generateJSFromCommunication());
            }
            
            //$core0 = array("isOccupied" => true, "isDevil" => true, "pName" => "h");
            //$core1 = array("isOccupied" => true, "isDevil" => true, "pName" => "p");
            //$cores = array($core0, $core1);
            //$viz->drawNode($cores, "core-1");
            //$viz->drawNode($cores, "core-2");
        ?>

                
        <!--
        <div id="node0" style="position: absolute; block; width:100px; height:100px; border:1px solid red; left:10px; top:100px">Box 0</div>
        <div id="node1" style="position: absolute; width:100px; height:100px; border:1px solid red; left:300px; top:400px">Box 1</div>
        -->
	</article>
	
	<aside>
	
            
        <div id="control">
            <input id="togglec" type="button" value="Toggle Connections" />
        </div>
        <br />

        <?php
            //print_r($viz->getStats());
            //print_r($viz->getObjectiveFunctionValue());
            echo '<div id="fingerprint-wrapper">';
            echo '<img width="150" height="150" class="fingerprint" src="./fingerprint.php?nodesize=20&type=random&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" /> <br />';
            //echo '<img class="fingerprint" src="./fingerprint.php?nodesize=20&type=order&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" />';
            echo '<img width="150" height="150" class="fingerprint" src="./fingerprint.php?nodesize=20&type=specterum&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" /> <br />';
            
            $stat = $viz->getStats();
            foreach ($stat as $key=>$val)
            {
                $$key = $val;
            }
            
            $d = $numContendedNodes / $numNodes;
            $c = $numCoScheduledBuddies / $numNodes;
            $o = $numPoweredOnNodes / $numNodes;
            
            $sumw = $wd + $wp + $wc;
            $wd /= $sumw;
            $wp /= $sumw;
            $wc /= $sumw;
            
            $obj = (($wd * $d) - ($wc * $c) + ($wp * $o));
            
            // This is global minimum 
            $min = (-$wc * floor($numProcesses / 2.0) / $numNodes) + ($wp * ceil($numProcesses / 2.0) / $numNodes);
            $max = ($wd * floor($numProcesses / 2.0) / $numNodes) + ((1.0) * $wp);
            
            $obj = 1.0 - ($obj / ($max - $min));
            
            $img = "http://chart.apis.google.com/chart?hey&chxl=0:|D|C|P|O&chxr=1,0,1&chxt=x,y&chs=150x150&cht=rs&chco=FF0000&chds=0,1&chd=t:$d,$c,$o,$obj,$d&chls=2&chm=B,FF000080,0,0,0";
            echo '<img class="fingerprint-chart" src="'.$img.'" alt="" border="0"/>';
            echo '</div>';
        ?>
	</aside>
    <br clear="all" style="clear: both" />

</div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js"></script>
<script type="text/javascript" src="./_/js/jquery.jsPlumb-1.3.7-all-min.js "></script>

<!-- this is where we put our custom functions -->
<script type="text/javascript">
    (function($){})(window.jQuery);
    jsPlumb.bind("ready", function() {  
        return;
        <?php echo $viz->generateJSONForConnections(false); ?> 
        for (var row in conn)
        {
            var node = conn[row];
            var sourceStr = node['htmlid'];
            var buddiesStr = node['buddies_htmlids'];
            
            //$("pre#debug").append('Source : ' + sourceStr);
            //$("pre#debug").append('| Buddies : ' + buddiesStr + '<br />');
            if (buddiesStr.length == 0) continue;
            var buddies = buddiesStr.split(",");
            
            for (var col in buddies)
            {            
                var buddy = buddies[col];
                var rint = Math.round(0xffffff * Math.random());
                var color = ('#0' + rint.toString(16)).replace(/^#0([0-9a-f]{6})$/i, '#$1');
                jsPlumb.connect({ 
                    source: sourceStr, 
                    anchor: "AutoDefault",
                    target: buddy, 
                    anchor: "AutoDefault",
                    endpoint: "Blank",
                    connector: [ "StateMachine", 50],     
                    paintStyle: { strokeStyle: "gray", lineWidth:4 }
                });
            }
        }
    });
      
    /* trigger when page is ready */
    
    
    $(document).ready(function (){
       
       //$(".core").css({ opacity: 0.75});  
       
       $("#togglec").hover(function () {
          <?php echo $viz->generateJSONForConnections(false); ?> 
            for (var row in conn)
            {
                var node = conn[row];
                var sourceStr = node['htmlid'];
                var buddiesStr = node['buddies_htmlids'];

                //$("pre#debug").append('Source : ' + sourceStr);
                //$("pre#debug").append('| Buddies : ' + buddiesStr + '<br />');
                if (buddiesStr.length == 0) continue;
                var buddies = buddiesStr.split(",");

                for (var col in buddies)
                {            
                    var buddy = buddies[col];
                    var rint = Math.round(0xffffff * Math.random());
                    var color = ('#0' + rint.toString(16)).replace(/^#0([0-9a-f]{6})$/i, '#$1');
                    jsPlumb.connect({ 
                        source: sourceStr, 
                        anchor: "AutoDefault",
                        target: buddy, 
                        anchor: "AutoDefault",
                        endpoint: "Blank",
                        //overlays: [["Arrow", {location:1,width:20, length:20}]],
                        connector: [ "StateMachine", 50],     
                        //connector: [ "Flowchart", 10],
                        //paintStyle: { strokeStyle: "black", lineWidth:4 }
                        paintStyle: { strokeStyle: color, lineWidth:4 }
                    });
                }
            }
       },
       function ()
       {
           jsPlumb.reset();
       }
       );
       
       
       $.fn.hilight = function(source) {      
           $(source).css("border-color", "black");
           $(source).stop(true, true, true).effect("highlight", {}, 1000);
            return $(this).each(function() {
                var sinkId = $(this).attr("id");                
                $(this).css("border-color", "black");
                $(this).stop(true, true, true).effect("highlight", {}, 1000);

                var sourceId = $(source).attr("id");
                //$("pre#debug").append(sourceId);
                jsPlumb.connect({ 
                    source: sourceId, 
                    anchor: "AutoDefault",
                    target: sinkId, 
                    anchor: "AutoDefault",
                    endpoint: "Blank",
                    connector: [ "StateMachine", 50],     
                    //connector: [ "Flowchart", 10],                  
                    paintStyle: { strokeStyle: "black", lineWidth:4 }
                });


                //jsPlumb.select({source:$(this).attr('id')}).hide();                
            });
        };
        
        $.fn.lolight = function(source) {
            var col = $(source).stop(true, true, true).css("background-color");
            $(source).css("border-color", col);
            return $(this).each(function() {
                var col = $(this).stop(true, true, true).css("background-color");
                $(this).css("border-color", col);
                jsPlumb.reset();
            });
        };
        
        //$(".node").width(50);
        //$(".node").height(50);
        
        $(".offnode").css("background-color", "#fafafa");
        $(".offnode").children().css("background-color", "#fafafa");
        <?php echo $viz->generateJQueryForHighlights(); ?>
    });
    
    
    
    
</script>
<?php require_once("./inc/footer.inc.php"); ?>