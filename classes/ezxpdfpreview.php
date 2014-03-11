<?php

class ezxpdfpreview
{
    var $Operators;
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
            'width' => array( 'type' => 'integer', 'required' => true, 'default' => 99 ),
            'height' => array( 'type' => 'integer', 'required' => true, 'default' => 0 ),
            'page' => array( 'type' => 'integer', 'required' => false, 'default' => 1 ),
            'original_filename' => array( 'type' => 'string', 'required' => false, 'default' => ''
             ) 
        ) );

    }
    /*!
     Executes the PHP function for the operator cleanup and modifies \a $operatorValue.
    */
    function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
    {
    	$pdffile = eZClusterFileHandler::instance( $operatorValue );
        if ( !$pdffile->exists() )
        {
            eZDebug::writeError( "File not readable or doesn't exist", "pdfpreview");
            return;
        }
        
        if ( $namedParameters['original_filename'] )
        {
            $mime = eZMimeType::findByURL( $namedParameters['original_filename'] );
            $filename = $mime['basename'].".png";
        }
        else
        {
            $mime = eZMimeType::findByURL( $operatorValue );
            $filename = $mime['basename'].".png";
        }
        $width = (int) $namedParameters['width'];
        if ( isset( $namedParameters['height'] ) and $namedParameters['height'] > 0 )
            $height = (int) $namedParameters['height'];
        else
            $height = (int) $namedParameters['width'];
        $page = (int) $namedParameters['page'] - 1;
        $source = $operatorValue;

        $dirPath = eZSys::cacheDirectory() . "/texttoimage/" . md5( $operatorValue . $page . $width . 'x' . $height );
        if ( !file_exists( $dirPath ) )
        {
            $ini =& eZINI::instance();
            $mod = $ini->variable( 'FileSettings', 'StorageDirPermissions' );
            eZDir::mkdir( $dirPath, octdec( $mod ), true );
        }
        $target = "$dirPath/$filename";
        if ( !file_exists( $target ) )
        {
        	$fileHandler = eZClusterFileHandler::instance( $target );
        	if ( $fileHandler->exists() )
        	{
        		$fileHandler->fetch(true);
        	}
        	else 
        	{
        		$pdffile->fetch(true);
        		$cmd =  "convert " . eZSys::escapeShellArgument( $source . "[" . $page . "]" ) . " " . "-resize " . eZSys::escapeShellArgument(  $width . "x" . $height . ">" ) . " " . eZSys::escapeShellArgument( $target );
            	$out = shell_exec( $cmd );
            	$fileHandler = eZClusterFileHandler::instance();
            	$fileHandler->fileStore( $target, 'pdfpreview-image', false );
            	eZDebug::writeDebug( $cmd, "pdfpreview" );
            	if ( $out )
            		eZDebug::writeDebug( $out, "pdfpreview" );
        	}
        }
        $operatorValue = $target;
    }
}
?>