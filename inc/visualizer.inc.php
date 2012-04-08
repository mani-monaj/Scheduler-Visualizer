<?php
class CVisualizer
{
    private $numNodes;
    private $numProcesses;
    private $coresPerNode;
    private $numCores;
    private $processNames;
        
    private $mX;    //Solution Matrix
    private $mP;    //Power Matrix
    private $mC, $mC_NS;    //Communication Matrix (+ non-symeteric)
    private $mD;    //Devil Matrix
    
    private $errorList;
    private $idMap;
    
    private $wc;
    private $wp;
    private $wd;      
    private $pon;
    private $ppr;
    
    // Statistics
    private $nodesData;
    private $numContendedNodes;
    private $numCoScheduledBuddies;
    private $numPoweredOnNodes;
    private $numFullUtilizedNodes;
    
    private $cache;
    
    function init($numnodes, $corespernode, $namesfile, $devilfile, $commfile, $solfile, $paramfile = "") {
        $this->errorList = array();
        $this->idMap = array();
        
        $this->numNodes = $numnodes;
        $this->coresPerNode = $corespernode;
        $this->numCores = $numnodes * $corespernode;
        
        
        $this->processNames = array();
        $this->mX = array();
        $this->mC = array();
        $this->mD = array();        
        
        list($this->numProcesses, $col) = $this->readMatrixFromFile($this->mD, $devilfile, "\n", " ");
        
        if ($this->numProcesses == 0)
        {
            $this->logError("Error : Number of processes in $devilfile is 0!");
            return false;
        }
                
        
        if ($col != 2)
        {
            $this->logError("Error : Format of $devilfile is incorrect.");
            return false;
        }
        
        list($row, $col) = $this->readMatrixFromFile($this->processNames, $namesfile, "\n", " ");

        if ($row == 0)
        {
            $this->proccessNames = array();
            $this->logError("Warning: problem with the $namesfile. Using dummy names.");
            for ($i = 0; $i < $this->numProcesses; $i++)
            {
                $this->processNames[$i][0] = $i;
                $this->processNames[$i][1] = sprintf("pr%d", $i);
            }
            $col = 2;
            
        }        
        else if ($row != $this->numProcesses)
        {
            $this->logError("Error : Number of rows in $namesfile ($row) is inconsistent with number of processes ($this->numProcesses)");
            return false; 
        }
        
        if ($col != 2)
        {
            $this->logError("Error : Format of $namesfile is incorrect.");
            return false;
        }
        
        if ($this->numProcesses > $this->numCores)
        {
            $this->logError("Error : Number of processes can not be more than cores");
            return false;
        }
        
        list($row, $col) = $this->readMatrixFromFile($this->mC, $commfile, "\n", " ");
        
        if (($row != $this->numProcesses) || ($col != $this->numProcesses))
        {
            $this->logError("Error: Communication matrix size should be ($this->numProcesses, $this->numProcesses), but it is ($row, $col).");
            return false;
        }
        
        
        $this->mC_NS = $this->mC;
        // Break down symetery in Communication Matrix to avoid duplicates
        for ($i = 0; $i < $this->numProcesses; $i++)
        {
            for ($j = 0; $j < $this->numProcesses; $j++)
            {
                if ($i >= $j)
                {
                    $this->mC_NS[$i][$j] = 0;
                }
            }
        }
        
        list($row, $col) = $this->readMatrixFromFile($this->mX, $solfile, "\n", " ");
        
        if (($row != $this->numCores) || ($col != $this->numProcesses))
        {
            $this->logError("Error: Solution matrix size should be ($this->numCores, $this->numProcesses), but it is ($row, $col).");
            return false;
        }
    
        if (!empty($paramfile)) {
            $dummy = array();
            list($row, $col) = $this->readMatrixFromFile($dummy, $paramfile, "\n", " ");
            
            if (($row == 1) && ($col == 3))
            {
                $this->wp = abs($dummy[0][0]);
                $this->wd = abs($dummy[0][1]);
                $this->wc = abs($dummy[0][2]);
            }
            else if (($row == 3) && ($col == 1))
            {
                $this->wp = abs($dummy[0][0]);
                $this->wd = abs($dummy[1][0]);
                $this->wc = abs($dummy[2][0]);
            }
            else //if (($row != 1) || ($col != 3))
            {
                $this->logError("Error: param matrix size should be (1, 3), but it is ($row, $col).");
                return false;
            }
            
            
            
            //Arash: obj = wd*devil+wc*commpai+wp*node_on ; wd > 0, wc < 0, wp > 0; Min 
            //
            //$this->pon = $dummy[0][3];
            //$this->ppr = $dummy[0][4];
        }
        $this->analyse();
        $this->saveDataToFile("./cache/".substr(md5($solfile),0,7).".cache");
        return true;
    }
       
    private function saveDataToFile($filename)
    {
        $this->cache = $filename;
        $data = array("cooff" => array($this->wp, $this->wd, $this->wc), "nodes" => $this->nodesData);
        file_put_contents($filename, serialize($data));
        //echo "Printed Data to '$filename' base64:" . base64_encode($filename);
    }
    
    public function getCacheFilename()
    {
        return $this->cache;
    }
    
    public function getStats()
    {
        return array(
            "wp" => $this->wp,
            "wd" => $this->wd,
            "wc" => $this->wc,
            "numNodes" => $this->numNodes,
            "numCores" => $this->numCores,
            "coresPerNode" => $this->coresPerNode,
            "numContendedNodes" => $this->numContendedNodes,
            "numPoweredOnNodes" => $this->numPoweredOnNodes,
            "numCoScheduledBuddies" => $this->numCoScheduledBuddies,
            "numFullUtilizedNodes" => $this->numFullUtilizedNodes
            );
    }
    
    private function analyse()
    {
        $this->numContendedNodes = 0;
        $this->numCoScheduledBuddies = 0;
        $this->numPoweredOnNodes = 0;
        $this->numFullUtilizedNodes = 0;
        $this->nodesData = array();
        for ($i = 0; $i < $this->numNodes; $i++)
        {
            $powerOnCores = 0;
            $numDevils = 0;
            $this->nodesData[$i]["processes"] = array();
            $this->nodesData[$i]["cores"] = array();
            for ($j = 0; $j < $this->coresPerNode; $j++)
            {
                $index = ($i * $this->coresPerNode) + $j;    
                
                $process_id = array_search(1, $this->mX[$index]);
                if ($process_id === FALSE)
                {
                    $this->nodesData[$i]["cores"][$j]["isOccupied"] = false;                    
                }
                else
                {
                    $powerOnCores++;
                    if ($this->mD[$process_id][1])
                    {
                        $numDevils++;
                    }
                    $this->nodesData[$i]["cores"][$j]["isOccupied"] = true;
                    $this->nodesData[$i]["cores"][$j]["isDevil"] = $this->mD[$process_id][1];
                    $this->nodesData[$i]["cores"][$j]["pName"] = $this->processNames[$process_id][1];
                    $this->nodesData[$i]["cores"][$j]["index"] = $process_id;
                    $this->nodesData[$i]["processes"][] = $process_id;
                }
                
            } 
            
            $this->nodesData[$i]["numOnCores"] = $powerOnCores;
            $this->nodesData[$i]["numDevils"] = $numDevils;
            $this->nodesData[$i]["isContended"] = false;
            
            if ($this->nodesData[$i]["numDevils"] == $this->coresPerNode)
            {
                $this->nodesData[$i]["isContended"] = true;
                $this->numContendedNodes++;
            }
            
            $this->nodesData[$i]["isFullyUtilized"] = false;
            if ($this->nodesData[$i]["numOnCores"] == $this->coresPerNode)
            {
                $this->nodesData[$i]["isFullyUtilized"] = true;
                $this->numFullUtilizedNodes++;
            }
            
            $commBuddies = 0;
            foreach ($this->nodesData[$i]["processes"] as $p1)
            {
                foreach ($this->nodesData[$i]["processes"] as  $p2)
                {
                    if ($p1 == $p2) continue;
                    if ($this->mC_NS[$p1][$p2] == 1) 
                    {
                        $commBuddies++;
                    }
                }
            }
            
            $this->nodesData[$i]["commBuddies"] = $commBuddies;
            $this->nodesData[$i]["isCoscheduled"] = false;
            if ($this->nodesData[$i]["commBuddies"] == (($this->coresPerNode * ($this->coresPerNode - 1)) / 2))
            {
                $this->nodesData[$i]["isCoscheduled"] = true;
                $this->numCoScheduledBuddies++;
            }
            if ($powerOnCores > 0)
            {
                $this->numPoweredOnNodes++;
            }
       
            
        }
    }
    
    private function logError($str)
    {
        $this->errorList[] = $str;
    }

    public function getError()
    {
        return implode("<br />", $this->errorList);
    }
    
    public function getObjectiveFunctionValue() 
    {
        //Arash: obj = wd*devilpairs+wc*commpairs+wp*node_on ; wd > 0, wc < 0, wp > 0; Min 
        //Jessica: obj = wd*devilpairs+wc*commpairs+wp*node_off ; wd < 0, wc > 0, wp > 0; Max
        
        $arash = ($this->wd * $this->numContendedNodes) + (-$this->wc & $this->numCoScheduledBuddies) + ($this->wp * $this->numPoweredOnNodes);
        $jess  = 10.0 * (-$this->wd * $this->numContendedNodes) + ($this->wc & $this->numCoScheduledBuddies) + ($this->wp * ($this->numNodes - $this->numPoweredOnNodes));
        
        return array("arash" => $arash, "jess" => $jess);
    }
    /**
     * Reads CSV and imports it to a matrix
     * @param type $mat Pointer to the matrix
     * @param type $data  CSV Text
     * @param type $lineDel Line Delimiter
     * @param type $dataDel Coloumn Delimiter
     * @return an array of $row,$col
     */
    public function readMatrixFromString(&$mat, $data, $lineDel, $dataDel)
    {
        $row = 0;
        $col = 0;
        foreach(explode($lineDel, $data) as $line)
        {
            if (empty($line)) 
            {
                continue;
            }
            $mat[$row] = explode($dataDel, $line);
            $size = count($mat[$row]);
            if ($size > $col)
            {
                $col = $size;
            }            
            $row++;
        }        
        return array($row, $col);
    }
    
    public function readMatrixFromFile(&$mat, $fileName, $lineDel, $dataDel)
    {
        $string = @file_get_contents($fileName);
        if ($string === FALSE)
        {
            $this->logError("Warning : Error Reading $fileName");
            return array(0,0);
        }
        else
        {
            return $this->readMatrixFromString($mat, $string, $lineDel, $dataDel);
        }
    }
    
    public function drawNode($ni, $id, $class = "", $text = "", $break = false)
    {
        if ($break) 
        {
            echo '<br clear="all" style="clear: all;" />';
        }

        if ($this->nodesData[$ni]["numOnCores"] == 0) $class .= " offnode";
        echo '<div id="'.$id.'" class="node '.$class.'" >';
        
        if (!empty($text)) 
        {
            echo "<span>$text</span";
        }
        $i = 0;
        
        foreach ($this->nodesData[$ni]["cores"] as $core)
        {
            foreach($core as $key=>$val)
            {
                $$key = $val;
            }
                                    
            $class = "";
            if ($isOccupied)
            {                
                $class .= " occupied-core";
                if ($isDevil) 
                {
                    $class .= " devil";
                }
                $this->idMap[$index] = $id.'-c'.$i;
            }
            else
            {
                $class .= " empty-core";
            }
            
            
            echo '<div id="'.$id.'-c'.$i.'" class="core '.$class.'">';
            echo ($isOccupied) ? $pName : "";
            echo '</div>';
            $i++;
        }
        
        
        $dclass = ($this->nodesData[$ni]["isContended"]) ? "contention" : "";
        
        
        $cclass = ($this->nodesData[$ni]["isCoscheduled"]) ? "green" : "";
        $pclass = ($this->nodesData[$ni]["isFullyUtilized"]) ? "green" : "";
        echo '<br clear="all" style="clear: both;" />';
        echo '<div class="bar shared-resource '.$dclass.'"></div>';
        echo '<br clear="all" style="clear: both;" />';
        echo '<div class="bar comm-buddies '.$cclass.'"></div>';
        echo '<br clear="all" style="clear: both;" />';
        echo '<div class="bar power-util '.$pclass.'"></div>';
        echo '</div>';
        
    }
 
    public function visualize()
    {
        
        $this->idMap = array();
        $break_size = floor(sqrt($this->numNodes));
        for ($i = 0; $i < $this->numNodes; $i++)
        {
            $this->drawNode($i, "node-$i","", "", ($i > 0) && ($i % $break_size == 0));
        }
        
        //echo "<h2>Contented: $this->numContendedNodes, CoScheduled: $this->numCoScheduledBuddies, On: $this->numPoweredOnNodes, Fully Utilized: $this->numFullUtilizedNodes</h2>";
        //print_r($this->getObjectiveFunctionValue());
    }
    
    public function generateJSONForConnections($sym = true)
    {
        $js = "";
        $js .= "var conn = [\n";
        
        for ($index = 0; $index < $this->numProcesses; $index++)
        {
            $htmlid = $this->idMap[$index];
            $buddies = array_keys( $sym ? $this->mC[$index] : $this->mC_NS[$index], 1);            
            $buddies_htmlids = array();
            foreach ($buddies as $buddy)
            {
                $buddies_htmlids[] = $this->idMap[$buddy];
            }
            $buddies_str = implode(",", $buddies_htmlids);
            $js .= "{  htmlid: '$htmlid', buddies_htmlids: '$buddies_str'},\n";            
        }
        $js .= "];\n";
        return $js;
    }
    
    public function generateJQueryForHighlights($sym = true)
    {
        $base = sqrt($this->numNodes) * $this->coresPerNode;
        
        $coreSize = round(700 - ($base * 12)) / ($base);
        $js = "";
        $js .= "$('.core').width($coreSize);";
        $js .= "$('.core').height(".($coreSize / 2.0).");";
        $js .= "$('.bar').width(".($coreSize * 2.0 + $this->coresPerNode * 2.0).");";
        $js .= "$('.bar').height(".($coreSize / 6.0).");";
        for ($index = 0; $index < $this->numProcesses; $index++)
        {
            $htmlid = $this->idMap[$index];            
            $buddies = array_keys( $sym ? $this->mC[$index] : $this->mC_NS[$index], 1);
            if (empty($buddies)) continue;
            $buddies_htmlids = array();
            //$buddies_htmlids[] = $htmlid;
            foreach ($buddies as $buddy)
            {
                $buddies_htmlids[] = $this->idMap[$buddy];
            }
            $buddies_str = "#".implode(",#", $buddies_htmlids);
            $js .= sprintf("\t$('#%s').hover(
                function () { $('%s').hilight('#%s') }, 
                function () { $('%s').lolight('#%s') } );\n", 
                $htmlid, $buddies_str, $htmlid, $buddies_str, $htmlid);
        }
        return $js;
    }
        
    
    
}
?>
