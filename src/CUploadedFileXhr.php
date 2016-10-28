<?php
namespace dekuan\defileup;


/**
 *	Handle file uploads via XMLHttpRequest
 */
class CUploadedFileXhr extends CUploadedFileFieldName
{
	function __construct()
	{
		parent::__construct();
	}

	function saveUploadFile( $sDstFilePath, & $vStreamData = "" )
	{
		//
		//	sDstFilePath	- the destination filename, values:( full filename or null )
		//	vStreamData	- if the value of sDstFilePath is null, then,
		//				this function will copy file stream to vStreamData
		//	RETURN		- true / false
		//
		$bRet		= false;
		$nRealSize	= 0;
		$fpTemp		= null;
		$fpInput	= null;
		$fpTarget	= null;

		//	...
		$vStreamData = "";

		$fpTemp = tmpfile();
		if ( $fpTemp )
		{
			$fpInput = fopen( "php://input", "r" );
			if ( $fpInput )
			{
				$nRealSize = stream_copy_to_stream( $fpInput, $fpTemp );
				fclose( $fpInput );
			}

			if ( $nRealSize > 0 && $nRealSize == $this->getSize() )
			{
				if ( ! empty( $sDstFilePath ) )
				{
					$fpTarget = fopen( $sDstFilePath, "w" );
					if ( $fpTarget )
					{
						fseek( $fpTemp, 0, SEEK_SET );
						stream_copy_to_stream( $fpTemp, $fpTarget );
						fclose( $fpTarget );
					}
				}
				else
				{
					fseek( $fpTemp, 0, SEEK_SET );
					while ( ! feof( $fpTemp ) )
					{
						$vStreamData .= fread( $fpTemp, 8192 );
					}
				}
			}

			//	...
			fclose( $fpTemp );
		}

		return true;
	}
	function getName()
	{
		$sRet	= "";

		if ( isset( $_GET[ $this->m_sFieldName ] ) )
		{
			$sRet = $_GET[ $this->m_sFieldName ];
		}

		return $sRet;
	}
	function getSize()
	{
		$nRet	= 0;

		if ( isset( $_SERVER[ 'CONTENT_LENGTH' ] ) )
		{
			$nRet = intval( $_SERVER[ 'CONTENT_LENGTH' ] );
		}

		return $nRet;
	}
}

