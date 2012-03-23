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
	
	<article>
		<?php
            require_once("./inc/visualizer.inc.php");
            $viz = new CVisualizer(5, 10, 2);
            $core0 = array("isOccupied" => true, "isDevil" => true, "pName" => "h");
            $core1 = array("isOccupied" => true, "isDevil" => true, "pName" => "p");
            $cores = array($core0, $core1);
            $viz->drawNode($cores, "core-1");
            $viz->drawNode($cores, "core-2");
        ?>
		
        <!--
        <div id="node0" style="position: absolute; block; width:100px; height:100px; border:1px solid red; left:10px; top:100px">Box 0</div>
        <div id="node1" style="position: absolute; width:100px; height:100px; border:1px solid red; left:300px; top:400px">Box 1</div>
        -->
	</article>
	
	<aside>
	
		<h2>Sidebar Content</h2>
	
	</aside>
	
	<footer>
		
		<p><small>&copy; Copyright Your Name Here 2011. All Rights Reserved.</small></p>
		
	</footer>

</div>

<?php require_once("./inc/footer.inc.php"); ?>