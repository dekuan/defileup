<?php
namespace dekuan\defileup;


/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class CUploadedFileForm extends CUploadedFileFieldName
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
		$fpTarget	= null;
		$sTmpFilePath	= "";

		//	...
		$vStreamData = "";

		if ( ! empty( $sDstFilePath ) )
		{
			if ( move_uploaded_file( $_FILES[ $this->m_sFieldName ]['tmp_name'], $sDstFilePath ) )
			{
				$bRet = true;
			}
		}
		else
		{
			$sTmpFilePath = $_FILES[ $this->m_sFieldName ]['tmp_name'];
			$fpTarget = fopen( $sTmpFilePath, "rb" );
			if ( $fpTarget )
			{
				while ( ! feof( $fpTarget ) )
				{
					$vStreamData .= fread( $fpTarget, 8192 );
				}
				fclose( $fpTarget );
				unset( $fpTarget );

				//	...
				$bRet = true;
			}
		}

		return $bRet;
	}
	function getName()
	{
		$sRet	= "";

		if ( isset( $_FILES[ $this->m_sFieldName ]['name'] ) )
		{
			$sRet = $_FILES[ $this->m_sFieldName ]['name'];
		}

		return $sRet;
	}
	function getSize()
	{
		$nRet	= 0;

		if ( isset( $_FILES[ $this->m_sFieldName ]['size'] ) )
		{
			$nRet = intval( $_FILES[ $this->m_sFieldName ]['size'] );
		}
		if ( 0 == $nRet )
		{
			if ( isset( $_SERVER[ 'CONTENT_LENGTH' ] ) )
			{
				$nRet = intval( $_SERVER[ 'CONTENT_LENGTH' ] );
			}
		}

		return $nRet;
	}
}
