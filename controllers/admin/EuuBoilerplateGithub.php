<?php

class EuuBoilerplateGithubController extends ModuleAdminController
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
        Tools::redirectLink('https://www.github.com/emreuyguc');
    }
}