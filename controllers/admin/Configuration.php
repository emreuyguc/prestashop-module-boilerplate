<?php

namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

use AdminController;
use Configuration;
use HelperForm;
use HelperList;
use Tools;
use Context;

trait ConfigurationController
{
    public $_alerts = [];

    public function actionDefineInfo()
    {
        InfoModel::insertRow([
            'info' => Tools::getValue('info')
        ]);
        $this->_alerts[] = $this->displayConfirmation($this->l('Info Add'));
    }

    public function actionDeleteInfo()
    {
        InfoModel::deleteRow(sql_replace('id_info = ?', [Tools::getValue('id_info')]));
        $this->_alerts[] = $this->displayConfirmation($this->l('Info Delete'));
    }


    public function getContent()
    {
        $this->postProcess();
        return implode('', $this->_alerts) . $this->infoForm() . $this->infoList();
    }

    private function infoList()
    {
        $this->fields_list = array(
            'id_info' => array(
                'width' => 'auto',
                'orderby' => false,
                'title' => $this->l('id'),
                'type' => 'text',
                'search' => false,
            ),
            'info' => array(
                'width' => 'auto',
                'orderby' => false,
                'title' => $this->l('info'),
                'type' => 'text',
                'search' => false,
            )
        );

        $helperList = new HelperList();

        $helperList->simple_header = false; //For showing add and refresh button
        $helperList->identifier = 'id_info';
        $helperList->shopLinkType = '';
        $helperList->show_toolbar = false;
        $helperList->actions = array('delete');
        $helperList->no_link = true;
        $helperList->module = $this;
        $helperList->title = $this->l('info List');

        $helperList->token = Tools::getAdminTokenLite('AdminModules');
        $helperList->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $content = InfoModel::selectAll();
        $helperList->listTotal = count($content);

        return $helperList->generateList($content, $this->fields_list);
    }

    private function infoForm()
    {
        $helper = new HelperForm();
        $helper->show_toolbar = false;

        $helper->submit_action = 'defineInfo';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => array(
                'info' => '',
            ),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->infoFormFields()));
    }

    private function infoFormFields()
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Define Info'),
                    'icon' => 'icon-new',
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'name' => 'info',
                        'label' => $this->l('Info field'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    public function displayDeleteLink($token, $id)
    {
        return '<a href="' .
            $this->context->link->getAdminLink('AdminModules') . '&configure=' .
            $this->name . '&table=' .
            $this->name . '_api' . '&deleteInfo&id_info=' .
            $id . '" class="delete" >' .
            $this->l('Delete') . '
				<img src="../img/admin/delete.gif" alt="{$action}" />
			</a>';
    }


}