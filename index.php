<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once 'config.php';
require_once 'vendor/autoload.php';
/**
 * CONFIG OPTIONS
 */
$option_show_link_to_file=true; // show a red link on top of every file content (will be ignored on printing).
$option_show_path_on_header=false; // show the filename path on the header (will be printed)
$option_procces_txt_as_markdown=false; // process .txt files like as markdown
$option_new_page_after_every_file=true;  // new page after the content of every file ( false = continuous content printing )

?><!DOCTYPE HTML>
<html lang="es" class="sidebar-visible no-js">
    <head>
        <!-- Book generated using mdBook -->
        <meta charset="UTF-8">
        <title>txt2xhtml</title>
        
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


<div id="searchbox" style="float:right;" class="hidden-print">
<form action="./index.php" method="post">
  <input type="text" name="search" value=""><input type="submit" value="Search">
</form>
</div>

<?php
/***
 * txt2xhtml
 *
 * composer require erusev/parsedown
 */

$Parsedown = new Parsedown();

/**
 * Locates all files (*.txt + *.md) under $folder recursively
 * 
 */
$folder = "./pages";
$a_files = find_all_files($folder);

$s_files = "";
if (isset($_POST["search"]))
{
    $search=$_POST["search"];
}else{
    $search=".";
}

// IGNORED FILES
$a_ignored=array("applications.html","bitnami.css", "favicon.ico", ".gitignore", "./pages/changelog/ejemplos/xampp.txt");

// FILTER LIST STRING
if (sizeof($a_files) > 0)
{
    foreach ($a_files as $key => $file) 
    {
        // not in ignored array + $search string + not .xhtml file + just .md files
        if ( (!in_array($file, $a_ignored)) && (substr_count($file, $search)) && (substr($file,-6)!=".xhtml") &&  ( (substr($file,-3)==".md") || (substr($file,-4)==".txt"))  )
        {
            $s_files .= '' . $file."\r\n";
        }
    }
} else {
    $s_files .= "ERROR: No files found.\r\n";
    die("Add *.md files inside $folder folder.");
}
//echo nl2br($s_files); // DEBUG

// SUMMARY
$summary= "# Summary\n\n";
$lines = explode("\r\n", $s_files);
foreach ($lines as $key => $file)
{
    if (strlen($file)>4)
    {
        $title=substr($file,strlen($folder)+1);
        $title=str_replace('.md','',$title);
        $title=str_replace('.txt','',$title);
        $title=trim($title);
        $link=substr($file,strlen($folder));
        $summary.= "- [".$title."](./pages".$link.")\r\n";
    }
}

// TOC
echo '<div id="toc" class="hidden-print">';
echo "\r\n".'<h1><a href="./index.php">txt2xhtml <small><small>v2020-01-20</small></small></a></h1>';
$line=0;
$a_summary=explode("\r\n", $summary);
foreach ($a_summary as $key=>$val)
{
    if ($line>=0)
    {
        preg_match_all('/\((.*?)\)/', $val, $a_temp);
        $file=@substr($a_temp[0][0],1,-1);
        $file=trim($file);
        echo "\r\n".'<a href="#'.strtolower(substr(str_replace('/','/',$file),strlen($folder)+1)).'">'.substr(str_replace('/','/',$file),strlen($folder)+1).'</a><br>';
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
            echo "\r\n\r\n".'<center><a id="'.strtolower(substr(str_replace('/','/',$file),strlen($folder)+1)).'"></a></center>';
            echo "\r\n\r\n";
            // OPTIONAL - show original filename link
            if ($option_show_link_to_file)
            {
                echo '<center><div id="titulo"><a href="'.$folder.'/'.substr($file, 7).'" target="_blank">#FILE: '.substr(str_replace('/','/',$file),strlen($folder)+1).'</a></div></center>';
            }
            echo "\r\n\r\n";
            // OPTIONAL - show filename path on header
            if  ($option_show_path_on_header)
            {
                echo '<p style="color:red"><small><b>'.substr($file,strlen($folder)).'</b></small></p>';
            }
            
            // READING FILE
            $output=file_get_contents($file);
           
            // .MD FILEs
            if (substr($file,-3)==".md")
            { 
                // MARKDOWN PARSED TO HTML
                $output= $Parsedown->text($output);
                $output=str_replace("\n","\r\n",$output);// convert Mac/Linux to windows 
            }

            // .TXT FILES
            // OPTIONAL - process .txt files like as markdown
            if ($option_procces_txt_as_markdown && (substr($file,-4)==".txt"))
            {
                $output=str_replace("\r\n","\r\n\r\n",$output);
                $output= $Parsedown->text($output);
            }




            // SAVING TO .xhtml
            $fp = fopen(substr($file,0,strrpos($file,'.')).'.xhtml', 'w');
            fwrite($fp, $xhtml_header);
                      
            // MODIFICATIONS PER LINE
            $a_output=explode("\n",$output);// DONT CHANGE OR FAIL WITH MARKDOWN CODE LABELS
            $output_temp="";
            foreach ($a_output as $key=>$eachline){        

                // CENTER ALL IMAGES?
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

                // CORRECT WIKIS IMAGE LINKS FOR XHTML EPUB's
                if (substr_count($eachline, 'src="./')>0)
                {
                    $eachline=str_replace('src="./','src="../Images/',$eachline);
                }


                // DOKUWIKI INDEX (REMOVED)
                if (substr_count($eachline, '{{indexmenu')>0)
                {
                    $eachline="<p>&nbsp;</p>";
                }
                
               // TXT PROCESSING BEFORE SAVING TO XHTML
               if (substr($file,-4)==".txt")
               { 
                   $eachline=nl2br($eachline);
               }

                // SAVING XHTML                
                fwrite($fp, $eachline."\r\n");

                // MODIFICATIONS FOR SCREEN - NOT SAVED TO XHTML

                // CORRECT RELATIVE IMAGES
                if (substr_count($eachline, 'src="../Images/')>0)
                {
                    $eachline=str_replace('src="../Images/','src="'.substr($file,0,strrpos($file,'/')).'/',$eachline);
                }
                

                // SHOWED ON SCREEN
                echo $eachline;
                
            }
            fwrite($fp, $xhtml_footer);
            fclose($fp);

            if ($option_new_page_after_every_file)
            {
                echo '<hr class="new-page" style="border:1px solid white!important">';// FORCES NEW PAGE 
            }
        }
    }
    $line++;

}




///-----------------------------------------------------------------------
// FUNCTIONS
function find_all_files($dir)
{
    $result=array();
    $root = scandir($dir);
    foreach($root as $value)
    {
        if($value === '.' || $value === '..' || $value === '.gitignore' || substr_count($dir,'.git') || substr_count($dir,'.svn')  ) 
        {
            continue;
        }
        if(is_file("$dir/$value")) {
            if ((substr($value,-3)=='.md') || (substr($value,-4)=='.txt'))
            {
                $result[]="$dir/$value";
            }
            continue;
        }
        if ($value!=".git"){
            foreach(find_all_files("$dir/$value") as $value)
            {
                $result[]=$value;
            }
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