<?php
//Li9jYWNoZS8yY2IxYjQzLmNhY2hl
$cache = trim(stripslashes(base64_decode($_GET['cache'])));
$nodePixels = (int) $_GET['nodesize'];
if ($nodePixels <= 0) $nodePixels = 40;

$text = @file_get_contents($cache);
if (empty($text)) die;

$data = unserialize($text);
$nodes = $data["nodes"];
list($wp, $wd, $wc) = $data["cooff"];



$sum = $wp + $wd + $wc;
$wp /= $sum;
$wd /= $sum;
$wc /= $sum;

$numNodes = count($nodes);
$coresPerNode = count($nodes[0]["cores"]);
$numCores = $numNodes*$coresPerNode;

$break = floor(sqrt($numNodes));

$width = $break * $nodePixels;
$height = $width;



$pixels = $nodePixels * $nodePixels;
for ($i = 0; $i < $numNodes; $i++)
{
    $bad = 0;
    $good = 0;
    $d = $nodes[$i]["numDevils"];
    $on = $nodes[$i]["numOnCores"];
    $c = $nodes[$i]["commBuddies"];
    $nodes[$i]["vis"] = array();
    if ($on == 0)
    {
        $nodes[$i]["vis"]["bad"] = 0;
        $nodes[$i]["vis"]["good"] = 0;
    }
    else
    {
        $bad += ($d / $coresPerNode) * $pixels * $wd;
        $good += ($c / (($coresPerNode * ($coresPerNode - 1)) / 2) ) * $pixels * $wc;
        $tmp = (1.0 - ($on / $coresPerNode)) * $wp * $pixels;
        //$good += $pixels * $tmp;
        $bad +=  ($tmp);
        $nodes[$i]["vis"]["bad"] = $bad;
        $nodes[$i]["vis"]["good"] = $good;
    }
    
}

$img = imagecreatetruecolor($width, $height);

$colors = array();
$colors["green"] = imagecolorallocate($img, 0, 255, 0);
$colors["red"] = imagecolorallocate($img, 255, 0, 0); 
$colors["blank"] = imagecolorallocate($img, 0, 0, 0);
$colors["wasted"] = imagecolorallocate($img, 127, 127, 127);

for ($i = 0; $i < $numNodes; $i++)
{
    $offsetX = ($i % $break) * $nodePixels;
    $offsetY = floor(($i / $break)) * $nodePixels;
    
    $good = $nodes[$i]["vis"]["good"];
    $bad = $nodes[$i]["vis"]["bad"];
    
   
    for ($y = 0; $y < $nodePixels; $y++)
    {
        for ($x = 0; $x < $nodePixels; $x++)
        {
            if (($nodes[$i]["numOnCores"]) == 0)
            {
                $col = $colors["blank"];
            }
            else if ($good > 0)
            {
                $col = $colors["green"];
                $good--;
            }
            else if ($bad > 0)
            {
                $col = $colors["red"];
                $bad--;
            }
            else
            {
                $col = $colors["blank"];
            }
            imagesetpixel($img, round($x + $offsetX), round($y + $offsetY), $col);
        }
    }
    
}

header('Content-Type: image/png');
imagepng($img);
?>
