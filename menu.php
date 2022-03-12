<?php

/*
 * menu.php
 *
 * Copyright (c) 2021 Don Mankin (Foose, Fooser, Foosie)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is furnished to do
 * so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A
 * PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Visit https://opensource.org/licenses/MIT
*/

// start session before we do anything else
session_start();

///////////  functions ///////////////

function getFolderList($dir)
{
    $retval = [];
    
    // clear php file cache
    clearstatcache();
    
    // add trailing slash if missing
    if(substr($dir, -1) != "/") {
        $dir .= "/";
    }
    
    // open pointer to directory and read list of files
    $d = @dir($dir) or die("getFolderList: Failed opening directory {$dir} for reading");
    while(FALSE !== ($entry = $d->read())) {
        if($entry[0] == ".") continue;  // skip hidden files
        if(is_dir("{$dir}{$entry}")) {
            if (trim(basename(strtolower("{$dir}{$entry}")) != "menu")) {
                $retval[] = ['folder' => "{$dir}{$entry}"];
            };
        }
    }
    $d->close();
    return $retval;
}

function displayFolderList($folders,$server_root,$http_base,$current_dir) { 
    echo "<div id='images'>";
    echo "<ul>";
    foreach($folders as $fld) {
        if (!empty($fld['folder'])) {
            // get folder tree by removing $server_root from $current_dir
            $url = $http_base . str_replace($server_root, "",$current_dir) . "/" . basename($fld['folder']) . "/";
            echo "<li class=\"projbox\">"; ?>
            <a href="<?php echo $url;?>">&nbsp;<b><?php echo basename($fld['folder']);?></b></a> <?php
            echo "</li>";
            echo "&nbsp;&nbsp;";
        }
    }
    echo "</ul>";
    echo "</div>";
    echo "<br><br>";
}

function debug_console($textstr) { ?>
    <script>
        console.log("<?php echo $textstr;?>");
	</script> <?php
} ?>

<script>
    function GoBackToReferer(page) { 
        window.location.replace(page);
    }
</script> 

<!-- end of functions -->

<!DOCTYPE html>

<html>

<head>
    <link rel="icon" href="<?php echo $http_base.'/favicon.ico';?>" type="mage/x-icon"/>
    <link rel="shortcut icon" href="<?php echo $http_base.'/favicon.ico';?>" type="image/x-icon"/>
    <meta charset="UTF-8">
    <meta name="author" content="Don Mankin">
    <meta name="description" content="PHP Menu of folders">
    <meta name="keywords" content="PHP, HTML, CSS, JavaScript">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>

<div class="my_text">

<style>
ul  {
    list-style: none;
    color: #fff;
    font-weight: bold;
    margin: 0;
    padding: 0;
}
.projbox {
    padding: 0px 0px;
    margin: 10px;
    display: inline-block;
    box-sizing: border-box;
    max-height: 180px;
    width: 180px;
}
body
{
    margin:0 auto;
    padding:0px;
    text-align:center;
    width:100%;
    font-family: "Myriad Pro","Helvetica Neue",Helvetica,Arial,Sans-Serif;
    color:#ffffff;
    background-color:#8A4B08;
}
.my_text
{
    font-family:    "Myriad Pro","Helvetica Neue",Helvetica,Arial,Sans-Serif;
    font-size:      12px;
    font-weight:    bold;
}
a:link, a:visited {
    font-size: 12px;
    background-color: #8A4B08;
    color: white;
    text-decoration: none;
}
a:hover, a:active {
    font-size: 12px;
    background-color: #8A4B08;
    text-decoration: none;
} 
    
?>
</style>

<?php

// lets hog the memory
ini_set('memory_limit', '-1');

// set for 15 minutes
ini_set('max_execution_time', 450);

// resets the time limit value
set_time_limit(0);

// get base host server url
if ( isset( $_SERVER["HTTPS"] ) && strtolower( $_SERVER["HTTPS"] ) == "on" )
    $protocol = "https://";
else
    $protocol = "http://";
$http_base = $protocol . $_SERVER['HTTP_HOST'];
$current_page = $http_base . $_SERVER['REQUEST_URI'];    
  
// store our first menu page
if (!isset($_SESSION['foose_menu_root'])) {
    $menu_root = $current_page;
    $_SESSION['foose_menu_root'] = $menu_root;
}
else
    $menu_root = $_SESSION['foose_menu_root'];

// !!!!! debugging !!!!!
// unset($_SESSION['foose_menu_root']);

// get base host server url
if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) == "on")
    $protocol = "https://";
else
    $protocol = "http://";
$http_base = $protocol . $_SERVER['HTTP_HOST'];
$server_root = $_SERVER['DOCUMENT_ROOT'];

// get current directory
$current_dir = getcwd();
$url_root = $http_base . str_replace($server_root,"",str_replace("\\","/",$current_dir));

// get variables
$hard_dir = $current_dir . "/";

// make sure we know what we are talking about
if (!file_exists($hard_dir))
    die("");
  
// fetch image details - hard folder path
$folders = getFolderList($hard_dir);

// sort the images by newest first
// usort($folders, function($a, $b){ return(filemtime($a['file']) < filemtime($b['file'])); });

// sort the images alphabetically
usort($folders, function($a, $b){ return(basename($a['folder']) > basename($b['folder'])); });

// intro
echo "<br><br>";
echo "<font style='color:yellow'><div style='font-size:18'>Select one of the following folders</div></font>";

// no need to go back if not refered
if (getenv('HTTP_REFERER') != $menu_root) { ?>
    <a style=font-size:14px; href="<?php echo getenv('HTTP_REFERER');?>">&nbsp;<b>Previous Page</b></a>&nbsp;&nbsp;&nbsp; <?php
}
if ($current_page != $menu_root) { ?>
    <a style=font-size:14px; href="<?php echo $menu_root; ?>">&nbsp;<b>Start Over</b></a> <?php
} ?>
 
<br><br><?php

// display images
displayFolderList($folders,$server_root,$http_base,$current_dir);

?>

</div> <!--- class="my_text" -->
</body>
</html>
