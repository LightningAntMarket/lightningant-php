<?php
// +----------------------------------------------------------------------
// | LAPPHP [ WE CAN DO IT JUST LAP IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://LAPphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
      
/**
 * LAPPHP API模式定义
 */
return array(
    // 配置文件
    'config'    =>  array(
        LAP_PATH.'Conf/convention.php',   // 系统惯例配置
        CONF_PATH.'config'.CONF_EXT,      // 应用公共配置
    ),

    // 别名定义
    'alias'     =>  array(
        'LAP\Exception'         => CORE_PATH . 'Exception'.EXT,
        'LAP\Model'             => CORE_PATH . 'Model'.EXT,
        'LAP\Db'                => CORE_PATH . 'Db'.EXT,
        'LAP\Cache'             => CORE_PATH . 'Cache'.EXT,
        'LAP\Cache\Driver\File' => CORE_PATH . 'Cache/Driver/File'.EXT,
        'LAP\Storage'           => CORE_PATH . 'Storage'.EXT,
    ),

    // 函数和类文件
    'core'      =>  array(
        MODE_PATH.'Api/functions.php',
        COMMON_PATH.'Common/function.php',
        MODE_PATH . 'Api/App'.EXT,
        MODE_PATH . 'Api/Dispatcher'.EXT,
        MODE_PATH . 'Api/Controller'.EXT,
        CORE_PATH . 'Behavior'.EXT,
    ),
    // 行为扩展定义
    'tags'  =>  array(
    ),
);
