<?php require_once("./inc/header.inc.php");?>
<?php
$path = "./data";
if (!($handle = opendir($path)))
{
    die("Can not open data folder!");
}
?>
<div id="list" class="wrapper">
    <h2>Please select a solution from this list : </h2>
    <div>
        <form name="selection" action="./index.php" method="get">
            <select name="set">
                <?php 
                    while (false !== ($entry = readdir($handle))) {
                        if ($entry != "." && $entry != ".." && is_dir($path."/".$entry)) {
                            echo "<option value=\"$entry\">$entry</option>\n";
                        }
                    }
                    closedir($handle);
                ?>
            </select>
            <input id="submit" name="submit" type="submit" value="Go!" />
        </form>
    </div>
    <h2> Or upload your own solution.</h2>
    <div id="upload">
        <form enctype="multipart/form-data" action="uploader.php" method="POST">
            <input name="uploadedfile" type="file" />
        </form>
    </div>
</div>
<?php require_once("./inc/footer.inc.php"); ?>