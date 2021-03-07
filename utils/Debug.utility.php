<?php
namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

trait DebugUtility
{
	public function consoleLog($data){
		if( ModuleConsts::DEFAULT_CONFIGURATION['DEBUG_MODE'] == TRUE){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, ModuleConsts::TEMP_CONFIG['DEBUG_SERVER']);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, print_r($data,true));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_exec($ch);
			curl_close($ch);
		}
	}
}