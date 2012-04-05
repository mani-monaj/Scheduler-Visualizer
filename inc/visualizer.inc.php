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
    private $numContendedNodes;
    private $numCoScheduledBuddies;
    private $numPoweredOnNodes;
    private $numFullUtilizedNodes;
    
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
            if (($row != 1) || ($col != 5))
            {
                $this->logError("Error: param matrix size should be (1, 5), but it is ($row, $col).");
                return false;
            }
            
            $this->wc = $dummy[0][0];
            $this->wp = $dummy[0][1];
            $this->wd = $dummy[0][2];
            $this->pon = $dummy[0][3];
            $this->ppr = $dummy[0][4];
        }
        return true;
    }
    
    
    
    private function logError($str)
    {
        $this->errorList[] = $str;
    }

    public function getError()
    {
        return implode("<br />", $this->errorList);
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
    
    public function drawNode($cores, $id, $fullyutilized = false, $class = "", $text = "", $break = false)
    {
        if ($break) 
        {
            echo '<br clear="all" style="clear: all;" />';
        }

        echo '<div id="'.$id.'" class="node '.$class.'" >';
        
        if (!empty($text)) 
        {
            echo "<span>$text</span";
        }
        $i = 0;
        
        $proccessesInThisNode = array();
        $allDevil = true;
        foreach ($cores as $core)
        {
            $isDevil = false;
            foreach($core as $key=>$val)
            {
                $$key = $val;
            }
                        
            $allDevil = $allDevil & $isDevil;
            
            $class = "";
            if ($isOccupied)
            {                
                $proccessesInThisNode[] = $index;
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
        
        
        $dclass = ($allDevil == true) ? "contention" : "";
        if ($allDevil)
        {
            $this->numContendedNodes++;
        }
        
        //print_r($proccessesInThisNode);
        $commBuddies = 0;
        foreach ($proccessesInThisNode as $p1)
        {
            foreach ($proccessesInThisNode as  $p2)
            {
                if ($p1 == $p2) continue;
                if ($this->mC_NS[$p1][$p2] == 1) 
                {
                    $commBuddies++;
                }
            }
        }
        $this->numCoScheduledBuddies += $commBuddies;
        
        $cclass = ($commBuddies == (($this->coresPerNode * ($this->coresPerNode - 1)) / 2) ) ? "green" : "";
        $pclass = ($fullyutilized) ? "green" : "";
        echo '<br clear="all" style="clear: all;" />';
        echo '<div class="shared-resource '.$dclass.'">S</div>';
        echo '<div class="comm-buddies '.$cclass.'">C</div>';
        echo '<div class="power-util '.$pclass.'">P</div>';
        echo '</div>';
        
    }
 
    public function visualize()
    {
        $this->numContendedNodes = 0;
        $this->numCoScheduledBuddies = 0;
        $this->numPoweredOnNodes = 0;
        $this->numFullUtilizedNodes = 0;
        $this->idMap = array();
        $break_size = floor(sqrt($this->numNodes));
        for ($i = 0; $i < $this->numNodes; $i++)
        {
            $cores = array();
            $powerOnCores = 0;
            for ($j = 0; $j < $this->coresPerNode; $j++)
            {
                $index = ($i * $this->coresPerNode) + $j;    
                
                $process_id = array_search(1, $this->mX[$index]);
                if ($process_id === FALSE)
                {
                    $cores[$j]["isOccupied"] = false;                    
                }
                else
                {
                    $powerOnCores++;
                    $cores[$j]["isOccupied"] = true;
                    $cores[$j]["isDevil"] = $this->mD[$process_id][1];
                    $cores[$j]["pName"] = $this->processNames[$process_id][1];
                    $cores[$j]["index"] = $process_id;
                }
                
            } 
            if ($powerOnCores > 0)
            {
                $this->numPoweredOnNodes++;
            }
            if ($powerOnCores == $this->coresPerNode)
            {
                $this->numFullUtilizedNodes++;
            }
            $this->drawNode($cores, "node-$i", ($powerOnCores == $this->coresPerNode) ,"", "", ($i > 0) && ($i % $break_size == 0));
            
        }
        
        echo "<h2>Contented: $this->numContendedNodes, CoScheduled: $this->numCoScheduledBuddies, On: $this->numPoweredOnNodes, Fully Utilized: $this->numFullUtilizedNodes</h2>";
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
        $js = "";
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
