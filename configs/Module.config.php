<?php

namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

interface ModuleConfig
{
    const DEFAULT_CONFIGURATION = [
        'DEBUG_MODE' => TRUE,
    ];

    const TEMP_CONFIG = [
        'DEBUG_SERVER' => 'a6c196f01f5d.ngrok.io'
    ];

    const NAME = 'euu_boilerplate';
    const TAB = 'other';

    const VERSION = '1.0.0';
    const AUTHOR = 'euu';

    const NEED_INSTANCE = TRUE;

    const TABS = array(
        [
            'title' => 'Euu BoilerPlate',
            'controller' => 'euu_boilerplate',
            'tabs' => array(
                [
                    'title' => 'Info',
                    'icon' => 'info',
                    'controller' => 'EuuBoilerplateInfo'
                ]
            )
        ],
        [
            'title' => 'Euu Boilerplate Github',
            'controller' => 'EuuBoilerplateGithub',
            'parent_tab' => 'DEFAULT', //parent tab name
            'icon' => 'bug_report'
        ],
    );

    const USE_BOOTSTRAP = TRUE;

    const DISPLAY_NAME = 'Euu Prestashop Module BoilerPlate';
    const DESCRIPTION = 'Prestashop module boilerplate';
    const UNINSTALL_MSG = 'are you sure ?';

    const MIN_PS_VERSION = '1.6';
    const MAX_PS_VERSION = _PS_VERSION_;

    const USE_HOOKS = [
        'displayHeader'
    ];

    const CONTROLLERS = [
    ];


}