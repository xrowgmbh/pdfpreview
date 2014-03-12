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
        $pdf_file_path    = $operatorValue;
        $width            = (int)$namedParameters['width'];
        $height           = (int)$namedParameters['height'];
        $version          = (int)$namedParameters['attribute_version'];
        $attribute_id     = (int)$namedParameters['attribute_id'];
        //the page of the page which will be used for the preview image
        $page             = (int)$namedParameters['page'] - 1;
        $mod = $ini->variable( 'FileSettings', 'StorageDirPermissions' );

        //check if the pdf which is required for the preview is existing
        $pdffile = eZClusterFileHandler::instance( $pdf_file_path );
        if ( !$pdffile->exists() )
        {
            eZDebug::writeError( "File not readable or doesn't exist. Can not generate a preview of the attribute.", "pdfpreview");
            return false;
        }

        //check if the pdfpreview folder exists
        $preview_cache_folder = eZSys::cacheDirectory() . "/pdfpreview";
        if ( !file_exists( $preview_cache_folder ) )
        {
            eZDir::mkdir( $preview_cache_folder, octdec( $mod ), true );
        }

        //check if the subfolder for the attribute already exists
        $preview_cache_attribute_folder = $preview_cache_folder . "/" . $attribute_id;
        if ( !file_exists( $preview_cache_attribute_folder ) )
        {
            eZDir::mkdir( $preview_cache_attribute_folder, octdec( $mod ), true );
        }

        //path to the pdf preview image and initialize it
        $cacheImageFilePath = $preview_cache_attribute_folder . "/" . $version . "_" . $page . "_" . $width . "x" . $height . ".jpg";
        $cacheFile = eZClusterFileHandler::instance( $cacheImageFilePath );
        $args = compact( array( "cacheImageFilePath", "pdffile", "pdf_file_path", "width", "height", "page" ) );
        //create an image or do nothing
        $run_it = $cacheFile->processCache( array( 'ezxpdfpreview', 'previewRetrieve' ),
                                            array( 'ezxpdfpreview', 'previewGenerate' ), NULL, NULL, $args );

        //return the path
        $operatorValue = $cacheImageFilePath;
    }

    function previewRetrieve( $file, $mtime, $args )
    {
        //do nothing
    }

    function previewGenerate( $file, $args )
    {
        extract( $args );
        
        $pdffile->fetch(true);
        $cmd = "convert " . eZSys::escapeShellArgument( $pdf_file_path . "[" . $page . "]" ) . " " . "-resize " . eZSys::escapeShellArgument(  $width . "x" . $height . ">" ) . " " . eZSys::escapeShellArgument( $cacheImageFilePath );
        $out = shell_exec( $cmd );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $cacheImageFilePath, 'pdfpreview', false );
        eZDebug::writeDebug( $cmd, "pdfpreview" );
        if ( $out )
        {
            eZDebug::writeDebug( $out, "pdfpreview" );
        }
    }
}

?>