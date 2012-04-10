<?php require_once("./inc/header.inc.php");?>
<div class="wrapper">
	<article>
		<?php
            require_once("./inc/visualizer.inc.php");
            
            $set = $_GET['set'];            
            if (empty($set)) die("Can not run without solution set!");
            
            //$_n = (int) $_GET['nodes'];
            //if ($_n == 0) $nodes = 100;
            
            $_cpn = (int) $_GET['corespernode'];
            if ($_cpn == 0) $_cpn = 2;
            
            $viz = new CVisualizer();
            //if (!$viz->init(4, 2, "./data/dummy-names.csv", "./data/dummy-devils.csv", "./data/dummy-c.csv", "./data/dummy-x.csv"))
            //if (!$viz->init(100, 2, "", "./data/arash-d.csv", "./data/arash-c.csv", "./data/arash-x.csv"))
            if (!$viz->init($_cpn, "./data/$set/names.csv", "./data/$set/d.csv", "./data/$set/c.csv", "./data/$set/x.csv", "./data/$set/coeff.csv"))
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
	
            
<!--		<div id="control">
                    <input id="togglec" type="button" value="Toggle Connections" />
                </div>
        -->
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

<?php require_once("./inc/footer.inc.php"); ?>