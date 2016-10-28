<?php
namespace dekuan\defileup;


/**
 *	field name
 */
define( 'FILEUPLOADER_FIELDNAME',	'dekuanfile' );	//	<input type=file name="file" ...



/**
 *	get/set field name
 */
class CUploadedFileFieldName
{
	var $m_sFieldName	= FILEUPLOADER_FIELDNAME;	//	<input type=file name="qqfile" ...

	function __construct( $sFileName = null )
	{
		if ( null == $sFileName || ! is_string( $sFileName ) || strlen( $sFileName ) <= 0 )
		{
			$this->m_sFieldName = FILEUPLOADER_FIELDNAME;
		}
		else
		{
			$this->m_sFieldName = $sFileName;
		}
	}

	public function setFieldName( $sFieldName )
	{
		$this->m_sFieldName = $sFieldName ;
	}
}
