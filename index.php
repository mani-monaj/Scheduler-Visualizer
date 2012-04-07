<?php require_once("./inc/header.inc.php");?>
<div class="wrapper">
	<header>		
		<h1>Cluster Scheduling Visualizer</h1>
        <!--
        <nav>
		
			<ol>
				<li><a href="">Nav Link 1</a></li>
				<li><a href="">Nav Link 2</a></li>
				<li><a href="">Nav Link 3</a></li>
			</ol>
		
		</nav>
        -->
	</header>
	<br clear="all" style="clear: both" />
	<article>
		<?php
            require_once("./inc/visualizer.inc.php");
            
            $viz = new CVisualizer();
            //if (!$viz->init(4, 2, "./data/dummy-names.csv", "./data/dummy-devils.csv", "./data/dummy-c.csv", "./data/dummy-x.csv"))
            //if (!$viz->init(100, 2, "", "./data/arash-d.csv", "./data/arash-c.csv", "./data/arash-x.csv"))
            if (!$viz->init(100, 2, "", "./data/arash-1/d.csv", "./data/arash-1/c.csv", "./data/arash-1/x.csv", "./data/arash-1/coeff.csv"))
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
        
        <?php
            print_r($viz->getStats());
            print_r($viz->getObjectiveFunctionValue());
            echo '<div id="fingerprint-wrapper">';
            echo '<img class="fingerprint" src="./fingerprint.php?nodesize=20&type=random&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" />';
            //echo '<img class="fingerprint" src="./fingerprint.php?nodesize=20&type=order&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" />';
            echo '<img class="fingerprint" src="./fingerprint.php?nodesize=20&type=specterum&cache='.base64_encode($viz->getCacheFilename()).'" alt="" border="0" />';
            
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
            
            $min = -$wc * (($coresPerNode * ($coresPerNode - 1)) / 2);
            $max = ($wd * $coresPerNode) + ((1.0) * $coresPerNode * $wp);
            
            $obj = 1.0 - ($obj / ($max - $min));
            
            $img = "http://chart.apis.google.com/chart?chxl=0:|D|C|P|O&chxr=1,0,1&chxt=x,y&chs=200x200&cht=rs&chco=FF0000&chds=0,1&chd=t:$d,$c,$o,$obj,$d&chls=2&chm=B,FF000080,0,0,0";
            echo '<img class="fingerprint-chart" src="'.$img.'" alt="" border="0"/>';
            echo '</div>';
        ?>
	</aside>
	<br clear="all" style="clear: both" />
	<footer>
		
		<p><small>Footer Comes Here</small></p>
		<pre id="debug">
        <?php
        //echo nl2br($viz->generateJSFromCommunication());
        ?>
        </pre>
	</footer>

</div>

<?php require_once("./inc/footer.inc.php"); ?>