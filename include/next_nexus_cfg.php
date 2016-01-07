<?php
/**
 * Created by PhpStorm.
 * User: zcqian
 * Date: 16/1/7
 * Time: 下午2:55
 */


namespace PtConfig
{
    $nexus_config = function ($config_group, $config_name)
    {
        include realpath(dirname(__FILE__) . '../') . 'config/allconfig.php';
        return ${$config_group}[$config_name];
    };

    $mysql_host = $nexus_config('BASIC', 'mysql_host');
    $mysql_pass = $nexus_config('BASIC', 'mysql_pass');
    $mysql_user = $nexus_config('BASIC', 'mysql_user');
    $mysql_db = $nexus_config('BASIC', 'mysql_db');
    define('PtConfig\MYSQL_DSN', 'mysql:host='.$mysql_host.';dbname='.$mysql_db);
    define('PtConfig\MYSQL_USER', $mysql_user);
    define('PtConfig\MYSQL_PASS', $mysql_pass);
}


