<?php
namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

trait ModuleHooks
{
    public function _afterInstall() {
	InfoModel::createTable();
        return true;
    }

    public function _beforeInstall() {
        return true;
    }
	
}
	
