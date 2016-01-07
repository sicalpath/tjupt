<?php
/** next_base.php
 * Base utility functions for next gen pt
 * (c)2016 Zhongchao Qian
 */


$rootpath =  realpath(dirname(__FILE__) . '../');

include_once $rootpath . 'include/next_nexus_cfg.php';

function get_db_handle(){
    try{
        $dbh = new PDO(PtConfig\MYSQL_DSN, PtConfig\MYSQL_USER, PtConfig\MYSQL_PASS,
            array(
                PDO::ATTR_PERSISTENT => true
            ));
        return $dbh;
    } catch (PDOException $e) {
        die("Failed to connect to DB!");
    }
}
