<?php
/*
 *
 *  ostate =1  的情况下 只退换LAP(
 *  ostate =4  的情况下 如果是支付了邮费的订单 则推币和退换 人民币
 *  退币是一对一的    退币包含 自主模式 和随机 和竞拍模式 都是一对一
 *  LAP(直接到账  人民币的话 添加 人民币 增加记录表即可
 *  由于 操作涉及到的 资金问题 所以都开启事务
 *  只限制 支付的
 */
namespace Api\Controller;

use LAP\Controller;

class GoodsBackController extends CommonController
{


    /**
     * 入口
     */
    private $_good = '';
    private $_order = '';

    public function index()
    {
        $return = array('status' => 0, 'msg' => L('PUBLIC_FAILED'));//'失败'

        $oid = I('oid', 0, 'intval');//订单号

        $this->key = I('privkey');//秘要

        if ($oid && $this->token() && $this->_order = M('orders')->where("oid={$oid} and senduid={$this->uid} and ostate =5")->find()) {

            $this->_good = M('goods')->where("gid={$this->_order['gid']}")->find(); //$this->_order['gid']);  //商品的信息


            $this->handle( $this->_order['money']*(1-C('FEE')));

        }


        $this->ajaxReturn($return);

    }


    /**

     */
    private function handle($rongbi)
    {

        $orderObject = M('orders');






        $orderObject->startTrans();//开启  订单对象

        // LAP(数组 start//
        $rongbiAdd['userId'] = $this->_order['uid']; //

        $rongbiAdd['score'] = $rongbi;

        $rongbiAdd['type'] = 10;  //退币类型

        $rongbiAdd['gid'] = $this->_good['gid'];

        $rongbiAdd['time'] = time();

        $rongbiAdd['detail'] =sprintf(L('_GOODSBACK_HANDLE_'), $this->_good['title']);





        $txid=$this->operateBlockBlance($this->_order,$this->key,0);


        $orderUpdate['txid'] = $txid;
        
        if (M('orders')->where("oid={$this->_order['oid']} and ostate=5 ")->find()&&$orderObject->save($orderUpdate)) {



            $return = array('msg' => L('PUBLIC_SUCCEED'), 'status' => 1);//'success'


        } else {



            $orderObject->rollback();
            $return = array('msg' => L('PUBLIC_FAILED'), 'status' => 2);//'error'

        }



        $this->ajaxReturn($return);
        // 钱数组 end//


    }


}