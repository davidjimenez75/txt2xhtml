<?php
/**
 * CONFIG OPTIONS
 */
$option_show_link_to_file=true; // show a red link on top of every file content (will be ignored on printing).
$option_show_path_on_header=false; // show the filename path on the header (will be printed)
$option_procces_txt_as_markdown=true; // process .txt files like as markdown
$option_new_page_after_every_file=true;  // new page after the content of every file ( false = continuous content printing )

// GLOBAL HEADER FOR XHTML
$xhtml_header='<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html>

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:epub="http://www.idpf.org/2007/ops">
<head>
  <title></title>
  <link rel="stylesheet" href="../Styles/style.css" type="text/css"/>
</head>

<body>
  
';

// GLOBAL FOOTER FOR XHTML
$xhtml_footer='

</body>
</html>';