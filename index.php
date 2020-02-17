<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'vendor/autoload.php';
?><!DOCTYPE HTML>
<html lang="es" class="sidebar-visible no-js">
    <head>
        <!-- Book generated using mdBook -->
        <meta charset="UTF-8">
        <title>md2xhtml</title>
        

        <meta content="text/html; charset=utf-8" http-equiv="Content-Type">
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#ffffff" />

        <link rel="shortcut icon" href="favicon.png">
        <link rel="stylesheet" href="css/variables.css">
        <link rel="stylesheet" href="css/general.css">
        <link rel="stylesheet" href="css/chrome.css">
        <link rel="stylesheet" href="css/print.css" media="print">

        <!-- Fonts -->
        <link rel="stylesheet" href="FontAwesome/css/font-awesome.css">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,700italic,800italic,400,300,600,700,800" rel="stylesheet" type="text/css">
        <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro:500" rel="stylesheet" type="text/css">

        <!-- Highlight.js Stylesheets -->
        <link rel="stylesheet" href="highlight.css">
        <!-- <link rel="stylesheet" href="tomorrow-night.css"> -->
        <!-- <link rel="stylesheet" href="ayu-highlight.css">  -->

        <!-- Custom theme stylesheets -->
        
        <link rel="stylesheet" href="custom.css">
        

        
    </head>
   <body>



<?php
require_once("mdbooks.php");
//echo "<hr>";
/***
 * wikimd2html
 *
 * composer require erusev/parsedown
 */

$Parsedown = new Parsedown();

/**
 * APUNTES
 * 
 * Locate all apuntes files an creates a book
 * 
 */
$folder = "./pages";
$a_files = find_all_files($folder);
$s_files = "";
if (isset($_POST["search"]))
{
    $search=$_POST["search"];
}else{
    $search=".md";
}

// IGNORED FILES
$a_ignored=array("applications.html","bitnami.css", "favicon.ico", "./pages/changelog/ejemplos/xampp.txt");

// FILTER LIST STRING
if (sizeof($a_files) > 0)
{
    foreach ($a_files as $key => $file) 
    {
        // not in ignored array + $search string + not .xhtml file + just .md files
        if ( (!in_array($file, $a_ignored)) && (substr_count($file, $search)) && (substr($file,-6)!=".xhtml") && (substr($file,-3)==".md")  )
        {
            $s_files .= '' . $file."\r\n";
        }
    }
} else {
    $s_files .= "ERROR: No files found.\r\n";
    die("Add *.md files inside $folder folder.");
}
//echo $s_files; // DEBUG

// SUMMARY
$summary= "# Summary\n\n";
$lines = explode("\r\n", $s_files);
foreach ($lines as $key => $file)
{
    if (strlen($file)>4)
    {
        $title=substr($file,strlen($folder)+1);
        $title=str_replace('.md','',$title);
        $title=trim($title);
        $link=substr($file,strlen($folder));
        $summary.= "- [".$title."](./pages".$link.")\r\n";
    }
}

// TOC
echo '<div id="toc" class="hidden-print">';
echo '<h1><a href="./index.php">md2xhtml</a></h1>';
$line=0;
$a_summary=explode("\r\n", $summary);
foreach ($a_summary as $key=>$val)
{
    if ($line>=0)
    {
        preg_match_all('/\((.*?)\)/', $val, $a_temp);
        $file=@substr($a_temp[0][0],1,-1);
        $file=trim($file);
        echo '<a href="#'.strtolower(substr(str_replace('/','/',$file),strlen($folder)+1)).'">'.substr(str_replace('/','/',$file),strlen($folder)+1).'</a><br>';
    }
    $line++;
}
echo '</div>';


$line=0;
foreach ($a_summary as $key=>$val)
{

    if ($line>=0)
    {
        if (strlen($val)>1)
        {
            preg_match_all('/\((.*?)\)/', $val, $a_temp);
            //echo "<pre>".var_dump($a_temp)."</pre>"; // DEBUG
            $file=$a_temp[0][0];
            $file=trim($file);
            $file=substr($file,1,-1);
            echo '<center><a id="'.strtolower(substr(str_replace('/','/',$file),strlen($folder)+1)).'"></a>';
            echo '<div id="titulo"><a href="'.$folder.'/'.substr($file, 7).'" target="_blank">#FILE: '.substr(str_replace('/','/',$file),strlen($folder)+1).'</a></div></center>';
            $markdown=file_get_contents($file);
           
            // MD2HTML
            $output= $Parsedown->text($markdown);

            // SAVING TO .xhtml
            $fp = fopen(substr(strtolower($file),0,-3).'.xhtml', 'w');
            fwrite($fp, $xhtml_header);
                      
            // MODIFICATIONS PER LINE
            $a_output=explode("\n",$output);
            $output_temp="";
            foreach ($a_output as $key=>$eachline){        
                // CENTER ALL IMAGES
                if (substr_count($eachline, '<img ')>0)
                {
                    $eachline='<center>'.$eachline.'</center>';
                }
                // DOKUWIKI IMAGES
                if (substr_count($eachline, '{{ :')>0)
                {
                    $eachline=str_replace('{{ :','<center><img src="./Images/',$eachline);
                    $eachline=str_replace('?400 |}}','"></center>',$eachline);
                    $eachline=str_replace(':','/',$eachline);
                }

                // CORRECT WIKIS IMAGE LINKS FOR XHTML EPUB's
                if (substr_count($eachline, 'src="./assets/')>0)
                {
                    $eachline=str_replace('src="./assets/','src="../Images/',$eachline);
                }

                // DOKUWIKI INDEX (REMOVED)
                if (substr_count($eachline, '{{indexmenu')>0)
                {
                    $eachline="<p>&nbsp;</p>";
                }

                echo $eachline;
                fwrite($fp, $eachline."\r\n\r\n");
            }
            fwrite($fp, $xhtml_footer);
            fclose($fp);            
            echo '<hr class="new-page">';// FORCES NEW PAGE 
        }
    }
    $line++;

}




///-----------------------------------------------------------------------
// FUNCTIONS
function find_all_files($dir)
{
    $root = scandir($dir);
    foreach($root as $value)
    {
        if($value === '.' || $value === '..') {continue;}
        if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;}
        foreach(find_all_files("$dir/$value") as $value)
        {
            $result[]=$value;
        }
    }
    return $result;
}


?>

     
        <script src="elasticlunr.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="mark.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="searcher.js" type="text/javascript" charset="utf-8"></script>
        

        <script src="clipboard.min.js" type="text/javascript" charset="utf-8"></script>
        <script src="highlight.js" type="text/javascript" charset="utf-8"></script>
        <script src="book.js" type="text/javascript" charset="utf-8"></script>

        <!-- Custom JS scripts -->


</body>
</html>