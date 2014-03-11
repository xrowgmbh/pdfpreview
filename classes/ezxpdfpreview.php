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
                                              'attribute_version' => array( 'type' => 'integer', 'required' => true)
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
        $cacheImageFilePath = $preview_cache_attribute_folder . "/" . $version . "_" . $width . "x" . $height . ".jpg";
        $cacheFile = eZClusterFileHandler::instance( $cacheImageFilePath );

        //return the path or create the missing preview image
        $operatorValue = $cacheFile->processCache( array( 'ezxpdfpreview', 'previewRetrieve' ),
                                                   array( 'ezxpdfpreview', 'previewGenerate' ), NULL, NULL, array( "preview_image_path" => $cacheImageFilePath, "pdf" => $pdffile, "pdf_path" => $pdf_file_path, "width" => $width, "height" => $height ));
    }

    function previewRetrieve( $complete_file_path, $mtime, $variables )
    {
        //only throw back the path of the existing "cached" preview image
        return $variables["preview_image_path"];
    }

    function previewGenerate( $complete_file_path, $variables )
    {
        $preview_image_path = $variables["preview_image_path"];
        $variables["pdf"]->fetch(true);
        $cmd = "convert " . eZSys::escapeShellArgument( $variables["pdf_path"] ) . " -resize " . eZSys::escapeShellArgument(  $variables["width"] . "x" . $variables["height"] . " > " ) . eZSys::escapeShellArgument( $preview_image_path );
        $out = shell_exec( $cmd );
        $fileHandler = eZClusterFileHandler::instance();
        $fileHandler->fileStore( $preview_image_path, 'pdfpreview', false );
        eZDebug::writeDebug( $cmd, "pdfpreview" );
        if ( $out )
        {
            eZDebug::writeDebug( $out, "pdfpreview" );
        }
    }
}

?>