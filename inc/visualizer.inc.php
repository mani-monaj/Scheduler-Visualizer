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
    
    
    function __construct($numnodes, $numprocesses, $corespernode) {
        $this->numNodes = $numnodes;
        $this->numProcesses = $numprocesses;
        $this->coresPerNode = $corespernode;
        $this->numCores = $numnodes * $corespernode;
        
        if ($this->numProcesses > $this->numCores)
        {
            echo "Warning : Number of processes can not be more than cores";
            return false;
        }
        
        $this->processNames = array();
        return true;
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
            echo "Warning : Error Reading $fileName";
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
        
}
?>
