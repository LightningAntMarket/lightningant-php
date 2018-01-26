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
 * LAPPHP Lite模式定义
 */
return array(
    // 配置文件
    'config'    =>  array(
        MODE_PATH.'Lite/convention.php', // 系统惯例配置
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
        MODE_PATH.'Lite/functions.php',
        COMMON_PATH.'Common/function.php',
        CORE_PATH . 'Hook'.EXT,
        CORE_PATH . 'App'.EXT,
        CORE_PATH . 'Dispatcher'.EXT,
        //CORE_PATH . 'Log'.EXT,
        CORE_PATH . 'Route'.EXT,
        CORE_PATH . 'Controller'.EXT,
        CORE_PATH . 'View'.EXT,
    ),
    // 行为扩展定义
    'tags'  =>  array(
    ),
);
