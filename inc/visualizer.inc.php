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
    private $mC;    //Communication Matrix
    private $mD;    //Devil Matrix
    
    private $errorList;
    private $idMap;
    
    function init($numnodes, $corespernode, $namesfile, $devilfile, $commfile, $solfile) {
        $this->errorList = array();
        $this->idMap = array();
        
        $this->numNodes = $numnodes;
        $this->coresPerNode = $corespernode;
        $this->numCores = $numnodes * $corespernode;
        
        
        $this->processNames = array();
        $this->mX = array();
        $this->mC = array();
        $this->mD = array();
        
        list($this->numProcesses, $col) = $this->readMatrixFromFile($this->processNames, $namesfile, "\n", " ");
              
        if ($this->numProcesses == 0)
        {
            $this->logError("Error : Number of processes in $namesfile is 0!");
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
        
        list($row, $col) = $this->readMatrixFromFile($this->mD, $devilfile, "\n", " ");
        
        if ($row != $this->numProcesses)
        {
            $this->logError("Error : Number of rows in $devilfile ($row) is inconsistent with number of processes ($this->numProcesses)");
            return false; 
        }
        
        if ($col != 2)
        {
            $this->logError("Error : Format of $devilfile is incorrect.");
            return false;
        }
        
        list($row, $col) = $this->readMatrixFromFile($this->mC, $commfile, "\n", " ");
        
        if (($row != $this->numProcesses) || ($col != $this->numProcesses))
        {
            $this->logError("Error: Communication matrix size should be ($this->numProcesses, $this->numProcesses), but it is ($row, $col).");
            return false;
        }
        
        list($row, $col) = $this->readMatrixFromFile($this->mX, $solfile, "\n", " ");
        
        if (($row != $this->numCores) || ($col != $this->numProcesses))
        {
            $this->logError("Error: Solution matrix size should be ($this->numCores, $this->numProcesses), but it is ($row, $col).");
            return false;
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
    
    public function drawNode($cores, $id, $class = "", $text = "")
    {
        echo '<div id="'.$id.'" class="node '.$class.'">';
        if (!empty($text)) 
        {
            echo "<span>$text</span";
        }
        $i = 0;
        
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
                $class .= " occupied-core";
                if ($isDevil) 
                {
                    $class .= " devil";
                }
                
//                $id = $core["id"];
//                $buddies = array_search($this->mC, "1");
//                if (!empty($buddies))
//                {
//                    $buddies_str = implode(",", $buddies);
//                    $text = 
//                }
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
        $class = ($allDevil == true) ? "contention" : "";
        echo '<br clear="all" style="clear: all;" />';
        echo '<div class="shared-resource '.$class.'"></div>';
        echo '</div>';
    }
 
    public function visualize()
    {
        $this->idMap = array();
        for ($i = 0; $i < $this->numNodes; $i++)
        {
            $cores = array();
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
                    $cores[$j]["isOccupied"] = true;
                    $cores[$j]["isDevil"] = $this->mD[$process_id][1];
                    $cores[$j]["pName"] = $this->processNames[$process_id][1];
                    $cores[$j]["index"] = $process_id;
                }
                
            }
            $this->drawNode($cores, "node-$i", "", "");
        }
    }
    
    public function generateJSFromCommunication()
    {
        $js = "";
        $js .= "var conn = [\n";
        
        for ($index = 0; $index < $this->numProcesses; $index++)
        {
            $htmlid = $this->idMap[$index];
            $buddies = array_keys($this->mC[$index], 1);            
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
}
?>
