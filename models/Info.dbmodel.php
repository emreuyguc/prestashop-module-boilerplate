<?php

namespace euu_boilerplate;

if (!defined('_PS_VERSION_')) exit;

use \Db;
use \DbQuery;

class InfoModel extends DbModel
{

    public $id_info;
    public $info;


    public static $_PREFIX = true;
    public static $definition = [
        'table' => 'euu_boilerplate_info',
        'primary' => 'id_info',
        'fields' => [
            'info' => [
                'type' => self::TYPE_STRING,
                'size' => 50,
                'required' => true,
            ]
        ],
    ];

}
