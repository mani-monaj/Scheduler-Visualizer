<?php
error_reporting(E_ALL & ~E_NOTICE);
require_once("./inc/utility.inc.php");
//Li9jYWNoZS8yY2IxYjQzLmNhY2hl
$cache = trim(stripslashes(base64_decode($_GET['cache'])));
$nodePixels = (int) $_GET['nodesize'];
if ($nodePixels <= 0) $nodePixels = 40;

$type = trim($_GET['type']);

if (empty($type)) $type = "random";

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

$min = -$wc * (($coresPerNode * ($coresPerNode - 1)) / 2);
$max = ($wd * $coresPerNode) + ((1.0) * $coresPerNode * $wp);

$hue_min = 250;
$hue_max = 10;
$sat = 100;
$ilu = 100;


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
        
        $obj = ($wd * $d) - ($wc * $c) + ($wp * ($on));
        $hue = (($obj - $min) / ($max - $min)) * ($hue_max - $hue_min);
        $hue += $hue_min;
        
        $nodes[$i]["vis"]["hue"] = $hue;
        //echo "$obj:$hue;";
    }
    
}
//echo "\nmin: $min, max: $max";
//die;

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
    
   
    $count = 0;
    $turn = 0;
    for ($y = 0; $y < $nodePixels; $y++)
    {
        for ($x = 0; $x < $nodePixels; $x++)
        {
            if ($type == "specterum")
            {
                if (($nodes[$i]["numOnCores"]) == 0)
                {
                    $col = $colors["blank"];
                }
                else
                {
                    list($rr, $gg, $bb) = GetRGB($nodes[$i]["vis"]["hue"], $sat, $ilu);
                    $col = imagecolorallocate($img, $rr, $gg, $bb);                    
                }
            }
            else
            {
                if (($nodes[$i]["numOnCores"]) == 0)
                {
                    $col = $colors["blank"];
                }
                else
                {
                    if ($type == "order")
                    {
                        $turn = ($count++ % 3);
                    }
                    else if ($type == "random")
                    {
                        $turn = (rand(0, 2));
                    }

                    if ($turn == 0)
                    {
                        if ($good > 0)
                        {
                            $col = $colors["green"];
                            $good--;
                        }
                        else
                        {
                            $turn = 1;
                        }
                    }


                    if ($turn == 1)
                    {
                        if ($bad  > 0)
                        {
                            $col = $colors["red"];
                            $bad--;
                        }
                        else
                        {
                            $turn = 2;
                        }
                    }


                    if ($turn == 2)
                    {
                        $col = $colors["blank"];
                    }
                }
            }
            imagesetpixel($img, round($x + $offsetX), round($y + $offsetY), $col);
        }
    }
    
}

header('Content-Type: image/png');
imagepng($img);
?>
