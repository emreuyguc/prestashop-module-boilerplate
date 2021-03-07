<?php

use euu_boilerplate\InfoModel;

class EuuBoilerplateInfoController extends ModuleAdminController
{

    public function __construct()
    {
        parent::__construct();
        $this->context = Context::getContext();
        $this->bootstrap = true;
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function renderList()
    {
        //dump(EuuTestDb::createTable());
    }
}