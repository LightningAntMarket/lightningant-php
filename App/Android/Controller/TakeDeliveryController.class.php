<?php

namespace Api\Controller;

use LAP\Controller;

/**
 * Class TakedeliveryController
 * @package Api\Controller
 * 确认 收货物
 */
class   TakeDeliveryController extends CommonController
{


    private $_good = '';

    private $_order = '';

    /**
     * 收货入口。
     */
    public function index()
    {

        $return = array('status' => 0,'msg'=>L('PUBLIC_USERERROR'));//用户错误


        if ($this->token()) {


            $oid = I('oid', -1, 'intval');// 订单号

            $this->key = I('privkey');//用户私钥

            $where['ostate'] = 3;
            $where['uid'] = $this->uid;
            $where['oid'] = $oid;

            $this->_order = $order = M('orders')->where($where)->find();


            if ($order) {  //查找订单号是否存在。
                $this->_good = M('goods')->where("gid={$this->_order['gid']}")->find();
                $this->handle( $this->_order['money']*(1-C('FEE')));

            }


            $return['msg'] = L('PUBLIC_NODATA'); //订单不存在  暂无数据

            $return['status'] = 3;

        }

        $this->response($return, $this->returnType);


    }

    /**
     * 开启事务  一个失败都不执行
     * 收货 给发布者   加LAP(
     */
    private function handle($rongbi)
    {

        $orderObject = M('orders');






        // LAP(数组 start//


        $rongbiAdd['userId'] = $this->_good['uid']; //


        $rongbiAdd['score'] = $rongbi;


        $rongbiAdd['type'] = 4;  //收货类型


        $rongbiAdd['gid'] = $this->_good['gid'];


        $rongbiAdd['time'] = time();
        $rongbiAdd['oid'] = $this->_order['oid'];


        $rongbiAdd['detail'] = sprintf(L('PUSH_7'), $this->_good['title']);

        //订单更新数组 start//
        $orderUpdate['ostate'] = 9;


        $orderUpdate['confirmtime'] = time();


        $txid = $this->operateBlockBlance($this->_order, $this->key);


        $orderUpdate['txid'] = $txid;


        $save['balance'] = array('exp', "balance+{$rongbi}");


        if (M('orders')->where("oid={$this->_order['oid']} and ostate=3 ")->find() && $orderObject->where("oid={$this->_order['oid']}")->save($orderUpdate) ) {




            $orderObject->commit();


            $return = array('msg' =>L('PUBLIC_SUCCEED'), 'status' => 1); //收货成功


            $array['cid'] = M('member')->where("uid={$this->_good['uid']}")->getField('cid'); //给谁加

            $array['uid'] = $this->_good['uid'];
            $array['gid'] =  $this->_good['gid'];
            $array['oid'] =    $this->_order['oid'];
            $array['type'] =10;
            $array['content'] = sprintf(L('PUSH_7'), $this->_good['title']);

            push($array);


        } else {

            $orderObject->rollback();
            $return = array('msg' => L('PUBLIC_FAILED'), 'status' => 2);//失败请联系客服


        }
       // $this->pushMsg();
        $this->ajaxReturn($return);

    }


}