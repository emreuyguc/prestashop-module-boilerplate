<?php
if (!defined('_PS_VERSION_')) exit;

require_once 'helpers/DbModel.helper.php';

require_once 'utils/Debug.utility.php';
require_once 'utils/Global.utility.php';

require_once 'configs/Module.config.php';
require_once 'controllers/admin/Configuration.php';
require_once 'hooks/Module.hooks.php';

#INCLUDE APP *MYAPP

#INCLUDE UTILITY *GLOBAL,TRAIT,CLASS

#INCLUDE MODELS *CLASS
require_once 'models/Info.dbmodel.php';

#INCLUDE TYPES *INTERFACE

#INCLUDE CONFIGS *INTERFACE

#INCLUDE HOOKS *TRAIT
require_once 'hooks/Template.hooks.php';

class euu_boilerplate extends Module implements euu_boilerplate\ModuleConfig
{
    use euu_boilerplate\ModuleHooks;
    use euu_boilerplate\ConfigurationController;
    use euu_boilerplate\DebugUtility;

    #USE HOOKS START
    use euu_boilerplate\TemplateHooks;

    #USE HOOKS USE END


    protected $_errors = [];

    public function __construct()
    {
        $this->name = self::NAME;
        $this->tab = self::TAB;
        $this->version = self::VERSION;
        $this->author = self::AUTHOR;
        $this->need_instance = self::NEED_INSTANCE;

        $this->bootstrap = self::USE_BOOTSTRAP;

        parent::__construct();

        $this->displayName = $this->l(self::DISPLAY_NAME);
        $this->description = $this->l(self::DESCRIPTION);
        $this->confirmUninstall = $this->l(self::UNINSTALL_MSG);

        $this->controllers = self::CONTROLLERS;

        $this->ps_versions_compliancy = array('min' => self::MIN_PS_VERSION, 'max' => self::MAX_PS_VERSION);
    }

    private function postProcess()
    {
        foreach (Tools::getAllValues() as $key => $value) {
            $method_name = 'action' . ucfirst($key);
            if (method_exists($this, $method_name)) {
                $this->$method_name();
            }
        }
    }

    public function enable($force_all = false)
    {
        return
            parent::enable($force_all)
            && $this->_installTabs();
    }

    public function disable($force_all = false)
    {
        return
            parent::disable($force_all)
            && $this->_uninstallTabs();
    }

    public function install()
    {
        return
            $this->_callModuleHook('_beforeInstall')
            && parent::install()
            && $this->_installTabs()
            && $this->_registerHooks()
            && $this->_initDefaultConfigurationValues()
            && $this->_callModuleHook('_afterInstall');
    }

    public function uninstall()
    {
        return
            $this->_callModuleHook('_beforeUninstall')
            && $this->_uninstallTabs()
            && $this->_unregisterHooks()
            && $this->_deleteConfigurationValues()
            && parent::uninstall()
            && $this->_callModuleHook('_afterUninstall');
    }

    private function _callModuleHook($hook)
    {
        if (method_exists($this, $hook)) {
            $this->{$hook}();
        }
        return true;
    }

    private function _registerHooks()
    {
        foreach (self::USE_HOOKS as $hook) {
            $this->registerHook($hook);
        }
        return true;
    }

    private function _unregisterHooks()
    {
        foreach (self::USE_HOOKS as $hook) {
            $this->unregisterHook($hook);
        }
        return true;
    }

    private function _initDefaultConfigurationValues()
    {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            $db_key = self::NAME . '_' . $key;
            if (self::DEFAULT_CONFIGURATION[$key] != Configuration::get($db_key)) {
                Configuration::updateValue($db_key, $value);
            }
        }
        return true;
    }

    private function _deleteConfigurationValues()
    {
        foreach (self::DEFAULT_CONFIGURATION as $key => $value) {
            $db_key = self::NAME . '_' . $key;
            Configuration::deleteByName($db_key);
        }
        return true;
    }

    private function _installTabs($tabs = null, $parent_tab_id = 0)
    {
        $tabs = $tabs ?? self::TABS;

        foreach ($tabs as $index => $defined_tab) {

            $id_tab = (int)Tab::getIdFromClassName($defined_tab['controller']);

            if (!$id_tab) {
                $id_tab = null;
            }

            $tab = new Tab($id_tab);
            $tab->class_name = $defined_tab['controller'];
            foreach (Language::getLanguages() as $lang) {
                $tab->name[$lang['id_lang']] = $defined_tab['title'];
            }

            $tab->id_parent = isset($defined_tab['parent_tab']) ? (int)Tab::getIdFromClassName($defined_tab['parent_tab']) : $parent_tab_id;
            $tab->position = $defined_tab['position'] ?? Tab::getNewLastPosition($tab->id_parent);
            $tab->icon = $defined_tab['icon'] ?? null;

            $tab->active = 1;
            $tab->module = $this->name;
            try {
                $tab->save();
            } catch (Exception $e) {
                $this->_errors[] = $e->getMessage();
                if (isset($defined_tab['tabs'])) {
                    break;
                }
            }

            if (isset($defined_tab['tabs'])) {
                $this->_installTabs($defined_tab['tabs'], $tab->id);
            }
        }

        if (count($this->_errors) > 0) {
            return false;
        }

        return true;
    }

    private function _uninstallTabs($tabs = null)
    {
        $tabs = $tabs ?? self::TABS;

        foreach ($tabs as $index => $defined_tab) {
            $id_tab = (int)Tab::getIdFromClassName($defined_tab['controller']);
            if ($id_tab) {
                $tab = new Tab($id_tab);
                try {
                    $tab->delete();
                } catch (Exception $e) {
                    $this->_errors[] = $e->getMessage();
                }
            }
            if (isset($defined_tab['tabs'])) {
                $this->_uninstallTabs($defined_tab['tabs']);
            }
        }

        if (count($this->_errors) > 0) {
            return false;
        }

        return true;
    }

}