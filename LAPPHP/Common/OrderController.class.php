<?php
namespace Android\Controller;


class OrderController extends CommonController
{


    /**
     * 抢到的
     * 一口价订单列表 2
     *
     * 竞拍列表  3
     */

    public function orderListByGet()
    {

        $return = array('status' => 0);

        if ($this->token()) {

            $modetype = I('modetype', 2, 'intval');
            if($modetype == 2)
            {
                //修改成已读
                already_read(['type'=>2,'uid'=>$this->uid]);
            }
            else if($modetype == 3)
            {
                //修改成已读
                already_read(['type'=>4,'uid'=>$this->uid]);
            }
            $where['orders.uid'] = $this->uid;

            $where['modetype'] = $modetype;

//            $where['joinBlockAddress'] = $this->user['blockaddress'];

            $page = new \Think\Page(1, 10);

            $limit = $page->firstRow . ',' . $page->listRows;

            $data = D('OrderGetView')->where($where)->order('time desc ')->limit($limit)->select();

            if ($data) {
                $return['status'] = 1;
                $return['data'] = $data;
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');
            }

        }

        $this->response($return, $this->returnType);

    }


    /**
     *
     * 竞拍 列表。
     * 进行中 和结束的
     * 不
     *
     */


    public function orderListGetAuction()
    {


        $return = array('status' => 0);

        if ($this->token()) {


            $page = new \Think\Page(1, 10);

            $limit = $page->firstRow . ',' . $page->listRows;

            $where['auction_record.uid'] = $this->uid;

            $is_sale = I('is_sale', 0, 'intval');   // 0是没 抢到  1是进行中的

            if ($is_sale) {

                $where['is_sale'] = 1;

            } else {

                $where['is_sale'] = 0;
                $where['is_get'] = 0;

            }
//            $where['joinBlockAddress'] = $this->user['blockaddress'];
            $data = D('OrderGetAuctionView')->where($where)->group('auction_record.gid')->order('time desc ')->limit($limit)->select();


            if ($data) {
                $return['status'] = 1;
                $return['data'] = $data;


            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');
            }

        }
        $this->response($return, $this->returnType);
    }

    /**
     * 抢到者 订单详情
     */

    public function orderDetailByGet()
    {
        $return = array('status' => 0);

        if ($this->token()) {

            $oid = I('oid', 0, 'intval');
            $where['orders.uid'] = $this->uid;
            $where['oid'] = $oid;

            $data = D('OrderGetView')->where($where)->find();


            $address_array = explode(',', $data['address']);


            if ($data) {

                $data['city'] = $address_array[0];
                $data['address'] = $address_array[1];
                $data['consignee'] = $address_array[2];
                $data['mobile'] = $address_array[3];

                $express_array = explode(',', $data['express']);

                $data['express_company'] = $express_array[0];
                $data['express_number'] = $express_array[1];


                $return['status'] = 1;
                $return['data'] = $data;
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');
            }


        }

        $this->response($return, $this->returnType);
    }


    /**
     * 送 的列表。
     */
    public function orderSendList()
    {


        $return = array('status' => 0);
        if ($this->token()) {
            //修改成已读
            already_read(['type'=>10,'uid'=>$this->uid]);
            $modetype = I('modetype', 2, 'intval');

            $where['goods.uid'] = $this->uid;

            $where['modetype'] = $modetype;

            $where['oid'] = array('gt', 0);

            $where['hasjoin'] = array('gt', 0);

            $page = new \Think\Page(1, 10);

            $limit = $page->firstRow . ',' . $page->listRows;

            $data = D('OrderSendView')->where($where)->order('time desc')->limit($limit)->select();

            if ($data) {
                $return['status'] = 1;
                $return['data'] = $data;
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');
            }


        }

        $this->response($return, $this->returnType);


    }


    /**
     *
     * 送的人 订单详情
     */

    public function orderSendDetail()
    {


        $return = array('status' => 0);
        if ($this->token()) {

            $oid = I('oid', 0, 'intval');
            $where['goods.uid'] = $this->uid;
            $where['oid'] = $oid;
            $data = D('OrderSendView')->where($where)->find();


            //  var_dump($data);


            // echo M()->getLastSql();
            if ($data) {
                $address_array = explode(',', $data['address']);


                $data['city'] = $address_array[0];
                $data['address'] = $address_array[1];
                $data['consignee'] = $address_array[2];
                $data['mobile'] = $address_array[3];


                $express_array = explode(',', $data['express']);
                $data['express_company'] = $express_array[0];
                $data['express_number'] = $express_array[1];


                $return['status'] = 1;
                $return['data'] = $data;
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');
            }

        }
        $this->response($return, $this->returnType);
    }


    /**
     * 发货。
     */


    public function send()
    {


        $return = array('status' => 0);
        if ($this->token()) {


            $where['senduid'] = $this->uid;

            $where['oid'] = I('oid', 0, 'intval');

            $save['sendtime'] = time();
            $save['express'] = I('company') . ',' . I('express');
            $save['ostate'] = 3;

            $data = M('orders')->where($where)->save($save);


            $order = M('orders')->where($where)->find(); // 抢到者uid


            $member = M('member')->find($order['uid']);




            $goods_info  = M('goods')->field('title,modetype')->find($order['gid']);


            $array['cid'] = $member['cid'];
            $array['uid'] = $member['uid'];;
            $array['gid'] =   $goods_info['gid'] ;
            $array['oid'] =   $where['oid'] ;
            $array['type'] = $goods_info['modetype']==2?9:8;


           
            $array['content'] = sprintf(L('PUSH_6'),$goods_info['title']);
            //push($array);


            if ($data) {
                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED');
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_FAILED');
            }

        }
        $this->response($return, $this->returnType);

    }

    /**
     *  申请退订单
     */


    public function applyReturnMoney()
    {


        $return = array('status' => 0);

        if ($this->token()) {


            $where['ostate'] = array('in', '1,3');   //发不发货都可以申请退币
            $where['uid'] = $this->uid;
            $where['oid'] = I('oid', 0, 'intval');


            $save['apply_return_time'] = time();

            $save['ostate'] = 5;


            $status = M('orders')->where($where)->save($save);


            if ($status) {
                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED');
            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_FAILED');
            }

        }
        $this->response($return, $this->returnType);

    }


    /**
     * 订单状态是1     对方没发货 的时候  并且订单超过了 七天， 就可以取消订单  直接到账
     */
    public  function applyCancleOrder(){
        $return = array('status' => 0);
        $oid = I('oid', 0, 'intval');//订单号

        $key= I('privkey');//秘要

        $time = time();
        //订单信息  买家
        if($this->token() && $key ) {

            if($orders = M('orders')->where("oid={$oid} and uid={$this->uid} and $time-time>259200 and ostate=1")->find()){

                $orderObject = M('orders');


                //订单更新数组 start//
                $orderUpdate['ostate'] = 7;
                $orderUpdate['returntime'] = time();

                $txid = $this->operateBlockBlance($orders, $key, 0);

                $orderUpdate['txid'] = $txid;

                if ($txid && $orderObject->where("oid={$orders['oid']}")->save($orderUpdate) ) {


                    $return = array('msg' => L('PUBLIC_SUCCEED'), 'status' => 1);

                } else {


                    $return = array('msg' => L('PUBLIC_FAILED'), 'status' => 2);

                }
            }else{
                $return['status']=2;
                $return['msg']=L('ORDER_1');
            }

        }
        $this->ajaxReturn($return);
    }


    /**
     *如果卖家发货了 买家不收货   14 天 可以确认收货
     */
    public  function  applyConfimOrder(){
        $return = array('status' => 0);
        $oid = I('oid', 0, 'intval');//订单号

        $key = I('privkey');//秘要

        $time = time();
        //订单信息 卖家
        if($this->token() && $key){

            if($orders=M('orders')->where("oid={$oid} and senduid={$this->uid} and ostate=3 and $time-sendtime>604800")->find()){
                $goods=M('goods')->where("gid={$orders['gid']}")->find();   //商品的信息 卖家


                $orderObject = M('orders');
                $memberObject = M('Member');
                $scoreObject = M('score');


                $memberObject->startTrans(); //开启  用户对象
                $scoreObject->startTrans();//开启  融币对象
                $orderObject->startTrans();//开启  订单对象

                // 融币数组 start//
                $rongbiAdd['userId'] = $goods['uid']; //
                $rongbiAdd['score'] = $goods['price'];
                $rongbiAdd['type'] = 4;  //收货类型
                $rongbiAdd['gid'] = $goods['gid'];
                $rongbiAdd['time'] = time();
                $rongbiAdd['detail'] = sprintf(L('PUSH_7'), $goods['title']);


                //订单更新数组 start//
                $orderUpdate['ostate'] = 9;
                $orderUpdate['confirmtime'] = time();

                $txid = $this->operateBlockBlance($orders, $key);
                $orderUpdate['txid'] = $txid;

                $save['balance'] = array('exp', "balance+{$goods['price']}");

                if ($txid && $memberObject->where("uid={$goods['uid']}")->save($save) && $orderObject->where("oid={$orders['oid']}")->save($orderUpdate) && $scoreObject->add($rongbiAdd)) {

                    $memberObject->commit();
                    $scoreObject->commit();
                    $orderObject->commit();

                    $return = array('msg' => L('PUBLIC_SUCCEED'), 'status' => 1);

                    $array['cid'] = M('member')->where("uid={$goods['uid']}")->getField('cid'); //给谁加
                    $array['uid'] = $goods['uid'];
                    $array['gid'] = 0;
                    $array['type'] = 7;
                    $array['content'] = sprintf(L('PUSH_7'), $goods['title']);
                    push($array);

                } else {
                    $memberObject->rollback();
                    $scoreObject->rollback();
                    $orderObject->rollback();
                    $return = array('msg' => L('PUBLIC_FAILED'), 'status' => 2);

                }
            }else{
                $return['status']=2;
                $return['msg']=L('ORDER_2');
            }

        }
        $this->ajaxReturn($return);

    }

    /**
     * 拒绝取消订单
     */

    public  function refuseCancleOrder(){

        $return = array('status' => 0);

        if ($this->token()) {


            $where['ostate'] =5;   //确保已经申请退币
            $where['senduid'] = $this->uid;
            $where['oid'] = I('oid', 0, 'intval');


            $save['apply_return_time'] = time();

            $save['ostate'] = 5;


            $order = M('orders')->where($where)->find();



            if($order){


                $save['ostate']= $order['sendtime']?3:1;  // 如果 已经发货了 还是 3 没发货是1

                $status=M('orders')->where($where)->save($save);
            }

            if ($status) {
                $return['status'] = 1;
            } else {

                $return['status'] = 2;
            }

        }
        $this->response($return, $this->returnType);
    }

    /**
     * 下架
     */


    public function delgood()
    {
        if ($this->token()) {


            $gid = I('gid', 0, 'intval');


            $uid = $this->uid;


            $where['uid'] = $uid;

            $where['gid'] = $gid;


            $goods=M('goods')->field('modetype')->where("gid=$gid")->find();

            if($goods['modetype']==2){ //一口价

                if (M('goods')->where($where)->setField('is_sale', 0)) {


                    $arr['status'] = 1;

                    $arr['msg'] = L('PUBLIC_LOAD');

                } else {

                    $arr['status'] = 2;

                    $arr['msg'] = L('PUBLIC_FAILED');

                }

            }elseif ($goods['modetype']==3){  //竞拍

                $auction = M('auction_record')->where("gid=$gid")->select();

                if(!$auction){ //有人出价
                    if (M('goods')->where($where)->setField('is_sale', 0)) {

                        $arr['status'] = 1;

                        $arr['msg'] = L('PUBLIC_LOAD');

                    } else {
                        $arr['status'] = 2;

                        $arr['msg'] = L('PUBLIC_FAILED');

                    }
                }else{

                    $arr['status'] = 2;

                    $arr['msg'] = L('ORDER_3'); //已经有人出价不允许下架
                }
            }

        }
        //返回结果
        $this->ajaxReturn($arr);
    }
}