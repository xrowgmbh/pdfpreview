<?php

class ezxpdfpreview
{
    function ezxpdfpreview()
    {
        $this->Operators = array( 'pdfpreview' );
    }

    /*!
     \return an array with the template operator name.
    */
    function operatorList()
    {
        return $this->Operators;
    }

    /*!
     \return true to tell the template engine that the parameter list exists per operator type,
             this is needed for operator classes that have multiple operators.
    */
    function namedParameterPerOperator()
    {
        return true;
    }
 
    /*!
     See eZTemplateOperator::namedParameterList
    */
    function namedParameterList()
    {
        return array( 'pdfpreview' => array( 
                                              'width' => array( 'type' => 'integer', 'required' => true ),
                                              'height' => array( 'type' => 'integer', 'required' => true ),
                                              'attribute_id' => array( 'type' => 'integer', 'required' => true),
                                              'attribute_version' => array( 'type' => 'integer', 'required' => true),
                                              'page' => array( 'type' => 'integer', 'required' => false, 'default' => 1)
                                            ) 
        );
    }

    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
        $ini              = eZINI::instance();
        $contentId    = $operatorValue;
        $width            = (int)$namedParameters['width'];
        $height           = (int)$namedParameters['height'];

        $container = ezpKernel::instance()->getServiceContainer();
        $pdfPreview = $container->get( 'xrow_pdf_preview' );

        $operatorValue = $pdfPreview->preview($contentId, $width, $height);
    }

    function previewRetrieve( $file, $mtime, $args )
    {
        //do nothing
    }

    function previewGenerate( $file, $args )
    {
        extract( $args );
        
        $pdffile->fetch(true);
        $cmd = "convert " . eZSys::escapeShellArgument( $pdf_file_path . "[" . $page . "]" ) . " " . " -alpha remove -resize " . eZSys::escapeShellArgument(  $width . "x" . $height ) . " " . eZSys::escapeShellArgument( $cacheImageFilePath );
        $out = shell_exec( $cmd );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $cacheImageFilePath, 'pdfpreview', false );
        eZDebug::writeDebug( $cmd, "pdfpreview" );
        if ( $out )
        {
            eZDebug::writeDebug( $out, "pdfpreview" );
        }
        
        //return values for the storecache function
        return array( 'content'  => $cacheImageFilePath,
                      'scope'    => 'pdfpreview',
                      'store'    => true );
    }
}

?>
