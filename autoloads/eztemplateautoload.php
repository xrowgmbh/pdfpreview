<?php

// Operator autoloading

$eZTemplateOperatorArray = array();

$eZTemplateOperatorArray[] =
  array( 'script' => 'extension/pdfpreview/classes/ezxpdfpreview.php',
         'class' => 'ezxpdfpreview',
         'operator_names' => array( 'pdfpreview' ) );

?>
