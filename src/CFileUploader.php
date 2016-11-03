<?php
namespace dekuan\defileup;

define( 'FILEUPLOADER_SUCCESS',					0 );
define( 'FILEUPLOADER_ERROR_UNKNOWN',			-100000 );
define( 'FILEUPLOADER_ERROR_NO_FILES',			10005 );	//	No files were uploaded.
define( 'FILEUPLOADER_ERROR_TOO_LARGE',			10006 );	//	File is too large
define( 'FILEUPLOADER_ERROR_NOT_ALLOWED',		10007 );
define( 'FILEUPLOADER_ERROR_EMPTY_FILE',		10028 );	//	File is empty
define( 'FILEUPLOADER_ERROR_DIR_UNWRITABLE',	10024 );	//	Upload directory isn't writable.
define( 'FILEUPLOADER_ERROR_PARAM',				10025);
define( 'FILEUPLOADER_ERROR_MOVEFILE',			10026 );
define( 'FILEUPLOADER_ERROR_SERVER_ERROR',		10027 );	//	server error

/**
 *	CLQQFileUploader
 **/
class CFileUploader extends CUploadedFileFieldName
{
	var $m_oFile			= null;
	var $m_ArrAllowFileExt	= null;		//	Array( 'jpg', 'jpeg', 'gif', 'png' );
	var $m_nLmtMaxSize		= 0;		//	10485760
	var $m_bOverwrite		= true;		//	overwrite if file exists

	function __construct()
	{
		parent::__construct();
		$this->m_sFieldName;
		//	...
		$this->m_oFile				= null;
		$this->m_ArrAllowFileExt	= Array( 'jpg', 'jpeg', 'png' ,'bmp');
		$this->m_nLmtMaxSize		= 1024*1024*2;	//	2M
		$this->m_bOverwrite			= true;

	}

	public function setAllowFileExt( $ArrAllowFileExt )
	{
		$this->m_ArrAllowFileExt = $ArrAllowFileExt;
	}
	public function setLmtMaxSize( $nLmtMaxSize )
	{
		//	in bytes
		$this->m_nLmtMaxSize = $nLmtMaxSize;
	}
	public function setOverwrite( $bOverwrite )
	{
		$this->m_bOverwrite = $bOverwrite;
	}

	public function isUploadSucc( $nErrorCode )
	{
		return ( FILEUPLOADER_SUCCESS == $nErrorCode );
	}
	public function saveUploadFile( $sDstFilePath , & $vStreamData = "" )
	{
		if ( $this->_isValidLmtMaxSize() )
		{
			if ( isset( $_FILES[ $this->m_sFieldName ] ) )
			{
				$this->m_oFile = new CUploadedFileForm();
				$this->m_oFile->setFieldName( $this->m_sFieldName );
			}
			else if ( isset( $_GET[ $this->m_sFieldName ] ) )
			{
				$this->m_oFile = new CUploadedFileXhr();
				$this->m_oFile->setFieldName( $this->m_sFieldName );
			}
		}
		//dd($this->m_sFieldName,$this->m_oFile,$this->_isValidLmtMaxSize());
//			sDstFilePath	- the destination filename, values:( full filename or null )
//			vStreamData	- if the value of sDstFilePath is null, then,
//						this function will copy file stream to vStreamData
//			RETURN		- errorid or succ as 0
//
//			$_FILE = Array
//			(
//				[qqfile] => Array
//				(
//					[name] => 2003-2010 pm2.5.png
//					[type] => image/jpeg
//					[tmp_name] => D:\soft\Wamp\tmp\php20BF.tmp
//					[error] => 0
//					[size] => 111631
//				)
//			)

		$nRet = FILEUPLOADER_ERROR_UNKNOWN;
		$sFinalDstFilePath = "";
		if ( ! $this->m_oFile )
		{
			//	No files were uploaded.
			return FILEUPLOADER_ERROR_NO_FILES;
		}
		if ( $this->m_oFile->getSize() > $this->m_nLmtMaxSize )
		{
			//	File is too large
			return FILEUPLOADER_ERROR_TOO_LARGE;
		}
		if ( 0 == $this->m_oFile->getSize() )
		{
			//	File is empty
			return FILEUPLOADER_ERROR_EMPTY_FILE;
		}
		if ( ! $this->_isAllowedFile() )
		{
			//	File extension isn't allowed
			return FILEUPLOADER_ERROR_NOT_ALLOWED;
		}


		if ( ! empty( $sDstFilePath ) )
		{
			//
			if ( $this->_isDirectoryWritable( $sDstFilePath ) )
			{
				$sFinalDstFilePath = $this->_getFinalDstFilePath( $sDstFilePath );
				if ( $this->m_oFile->saveUploadFile( $sFinalDstFilePath ) )
				{
					$nRet = FILEUPLOADER_SUCCESS;
				}
				else
				{
					//	Could not save uploaded file.
					//	The upload was cancelled, or server error encountered
					$nRet = FILEUPLOADER_ERROR_SERVER_ERROR;
				}
			}
			else
			{
				//	Server error. Upload directory isn't writable.
				$nRet = FILEUPLOADER_ERROR_DIR_UNWRITABLE;
			}
		}
		else
		{
			//	...
			$vStreamData = "";
			if ( $this->m_oFile->saveUploadFile( null, $vStreamData ) )
			{
				$nRet = FILEUPLOADER_SUCCESS;
			}
			else
			{
				//	Could not save uploaded file.
				//	The upload was cancelled, or server error encountered
				$nRet = FILEUPLOADER_ERROR_SERVER_ERROR;
			}
		}

		return $nRet;
	}

	public function getName()
	{
		if ( $this->m_oFile )
		{
			return $this->m_oFile->getName();
		}
	}
	public function getExt()
	{
		//	RETURN	'jpg', 'png', 'exe', ...
		$sRet		= "";
		$sFileName	= $this->getName();
		if ( ! empty( $sFileName ) )
		{
			$pDot = strrchr( $sFileName, '.' );
			if ( ! empty( $pDot ) )
			{
				$sRet = strtolower( substr( $pDot, 1 ) );
			}
		}

		return $sRet;
	}
	public function isImageExt()
	{
		//	...
		$bRet		= false;
		$ArrImageExt	= $this->m_ArrAllowFileExt;
		$sFileExt	= $this->getExt();
		if ( ! empty( $sFileExt ) )
		{
			if ( in_array( $sFileExt, $ArrImageExt ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}



	private function _isValidLmtMaxSize()
	{
		$bRet	= false;

		//	...
		$nMaxPostSize	= $this->_toBytes( ini_get( 'post_max_size' ) );
		$nMaxUploadSize	= $this->_toBytes( ini_get( 'upload_max_filesize') );

		if ( $this->m_nLmtMaxSize <= $nMaxPostSize && $this->m_nLmtMaxSize <= $nMaxUploadSize )
		{
			//$size = max( 1, $this->m_nLmtMaxSize / 1024 / 1024 ) . 'M';
			//die( "{'error':'increase post_max_size and upload_max_filesize to $size'}" );
			$bRet = true;
		}

		return $bRet;
	}

	private function _toBytes( $sString )
	{
		$nRet	= 0;

		$sString = trim( $sString );
		if ( ! empty( $sString ) )
		{
			$sLast = strtolower( substr( $sString, -1, 1 ) );
			if ( ! empty( $sLast ) )
			{
				$nRet = intval( substr( $sString, 0, -1 ) );
				switch( $sLast )
				{
					case 'g': $nRet *= 1024;
					case 'm': $nRet *= 1024;
					case 'k': $nRet *= 1024;
				}
			}
		}

		return $nRet;
	}

	private function _isDirectoryWritable( $sDstFilePath )
	{
		$bRet		= false;
		$ArrPathInfo	= Array();

		if ( ! empty( $sDstFilePath ) )
		{
			$ArrPathInfo = @pathinfo( $sDstFilePath );
			if ( is_array( $ArrPathInfo ) && isset( $ArrPathInfo['dirname'] ) )
			{
				if ( is_dir( $ArrPathInfo['dirname'] ) )
				{
					if ( is_writable( $ArrPathInfo['dirname'] ) )
					{
						$bRet = true;
					}
				}
			}
		}

		return $bRet;
	}


	private function _getFinalDstFilePath( $sDstFilePath )
	{
		$sRet		= $sDstFilePath;
		$ArrPathInfo	= Array();
		$sDirName	= "";
		$sBaseName	= "";
		$sExtension	= "";
		$sFileName	= "";

		if ( ! empty( $sDstFilePath ) )
		{
			$ArrPathInfo = @pathinfo( $sDstFilePath );
			if ( is_array( $ArrPathInfo ) &&
				isset( $ArrPathInfo['dirname'] ) &&
				isset( $ArrPathInfo['basename'] ) &&
				isset( $ArrPathInfo['extension'] ) )
			{
				$sDirName	= $ArrPathInfo['dirname'];
				$sBaseName	= $ArrPathInfo['basename'];
				$sExtension	= $ArrPathInfo['extension'];
				$sFileName	= isset( $ArrPathInfo['filename'] ) ? $ArrPathInfo['filename'] : basename( $sBaseName, ( '.' . $sExtension ) );

				if ( ! $this->m_bOverwrite )
				{
					//	don't overwrite previous files that were uploaded
					while ( file_exists( ( $sDirName . '/' . $sBaseName ) ) )
					{
						$sFileName .= rand( 1000, 9999 );
					}

					//	...
					$sRet = ( $sDirName . '/' . $sBaseName );
				}
			}
		}

		return $sRet;
	}

	private function _isAllowedFile()
	{
		//	QQFILEUPLOADER_ERROR_NOT_ALLOWED
		$bRet		= false;
		$sFileExt	= "";

		if ( ! $this->m_oFile )
		{
			return false;
		}

		$ArrPathInfo = @pathinfo( $this->m_oFile->getName() );
		if ( is_array( $ArrPathInfo ) &&
			isset( $ArrPathInfo[ 'extension' ] ) )
		{
			$sFileExt = strtolower( $ArrPathInfo[ 'extension' ] );

			if ( ! empty( $sFileExt ) &&
				in_array( $sFileExt, $this->m_ArrAllowFileExt ) )
			{
				$bRet = true;
			}
		}

		return $bRet;
	}
}