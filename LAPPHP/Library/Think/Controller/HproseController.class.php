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
namespace LAP\Controller;
/**
 * LAPPHP Hprose控制器类
 */
class HproseController {

    protected $allowMethodList  =   '';
    protected $crossDomain      =   false;
    protected $P3P              =   false;
    protected $get              =   true;
    protected $debug            =   false;

   /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        //导入类库
        Vendor('Hprose.HproseHttpServer');
        //实例化HproseHttpServer
        $server     =   new \HproseHttpServer();
        if($this->allowMethodList){
            $methods    =   $this->allowMethodList;
        }else{
            $methods    =   get_class_methods($this);
            $methods    =   array_diff($methods,array('__construct','__call','_initialize'));   
        }
        $server->addMethods($methods,$this);
        if(APP_DEBUG || $this->debug ) {
            $server->setDebugEnabled(true);
        }
        // Hprose设置
        $server->setCrossDomainEnabled($this->crossDomain);
        $server->setP3PEnabled($this->P3P);
        $server->setGetEnabled($this->get);
        // 启动server
        $server->start();
    }

    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method,$args){}
}