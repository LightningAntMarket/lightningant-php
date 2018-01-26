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
 * LAPPHP JsonRPC控制器类
 */
class JsonRpcController {

   /**
     * 架构函数
     * @access public
     */
    public function __construct() {
        //控制器初始化
        if(method_exists($this,'_initialize'))
            $this->_initialize();
        //导入类库
        Vendor('jsonRPC.jsonRPCServer');
        // 启动server
        \jsonRPCServer::handle($this);
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
