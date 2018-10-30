<?php
namespace Android\Controller;


class GoodsController extends CommonController
{

    protected $allowMethod = array('get', 'post', 'put'); // REST允许的请求类型列表

    protected $allowType = array('html', 'xml', 'json'); // REST允许请求的资源类型列表
    protected $goods; //
    protected $join; //


    public function detail()
    {

        $return = array('status' => 1, 'msg' => L('PUBLIC_FAILED'));

        $gid = I('gid', 1, 'intval');// 商品的id

        $where['gid'] = $gid;

        $this->goods = D('GoodsView')->where($where)->find();

        if ($this->goods) {

            M('goods')->where($where)->setInc('looknumber'); // 用户的积分加1

            $this->token();

            switch ($this->goods['modetype']) {
                case 2: //  一口价
                    $this->FixTypeJoin();
                    break;
                case 3://竞拍
                    $this->AuctionTypeJoin();
                    break;//
            }

            $return['goods'] = $this->goods;

            $return['goods']['image']=explode(',', $return['goods']['image']) ;
            $return['joinlog'] =  $this->join;
            $return['sendlog'] =  $this->sendlog(10,$this->goods['uid']);

            $num= M('review')->field("floor(AVG(logistics)) as logistics,floor(AVG (quality)) as quality,floor(AVG (communicate)) as communicate")->where("uid={$this->goods['uid']}")->find();

            $return['score']=sprintf("%.1f", array_sum($num)/count($num));

            $return['myBalance'] =$this->user['balance']? $this->user['balance']:0;





            $this->uid&&$return['isjoin'] =  D('Order')->isJoin($this->uid, $gid);



        } else {

            $return['status'] = 0;
            $return['msg'] = L('_GOODS_DETAIL_0');//'该商品已经下架';
        }

        $this->response($return, $this->returnType);

    }


    /**
     * 随机模式
     */
    private function FixTypeJoin()
    {

        $this->join= D('OrdersView')->where("gid={$this->goods['gid']} and member.uid>0 ")->order('oid desc')->limit(10)->select();

    }


    /**
     * 竞拍模式
     */


    private function AuctionTypeJoin()
    {

        $this->join= D('AuctionView')->where("gid={$this->goods['gid'] } and  member.uid>0 ")->order('money desc')->limit(10)->select();
        //echo  M()->getLastSql();

    }


    /**
     * 送的记录
     */
    public function sendlog($number = 0,$uid=0)
    {


        $page = new \Think\Page(1, 10);

        $limit = $number ? $number : "$page->firstRow,$page->listRows";

        $uid = $uid?$uid:I('get.uid',1, 'Intval');

        $field = array('gid', 'title','cover','price');


        $time=time();

        $data = M('goods')->field($field)->where("is_sale in (0,1) and uid={$uid}  and {$time}>up_time")->limit($limit)->order('gid desc')->select();//and gid!={$this->goods['gid']

        if ($data) {
            $return['data'] = $data;
            $return['status'] = 1;

        } elseif ($_GET['p'] > 0) { //判断是没有更多 的加载数据
            $return['status'] = 3;
            $return['data'] =array();
        } else {
            $return['status'] = 2;
            $return['data'] =null;
        }

        if ($number) {
            return $data;
        } else {
            $this->ajaxReturn($return);
        }

    }


    /**
     *  获取 一口价 更多的 参加者
     */

    public function getMoreOnePriceJoins()
    {
        $gid = I('get.gid', 0, 'Intval');
        $page = new \Think\Page(1, 10);

        $limit = $page->firstRow . ',' . $page->listRows;

        if ($data = D('OrdersView')->where("gid={$gid} and member.uid>0")->order('oid desc')->limit($limit)->select()) {
            $return['status'] = 1;
            $return['msg'] = L('PUBLIC_LOAD');//'加载成功';
            $return['data'] = $data;
        } else {

            $return['status'] = 0;
            $return['msg'] =L('PUBLIC_NODATA');  //'没有更多用户';
        }


        $this->response($return, $this->returnType);
    }

    /**
     *  获取更多
     */
    public function getMoreAuctionJoins()
    {
        $gid = I('get.gid', 0, 'Intval');
        $page = new \Think\Page(1, 10);

        $limit = $page->firstRow . ',' . $page->listRows;

        if ($data = D('AuctionView')->where("gid={$gid} and member.uid>0")->order('money desc')->limit($limit)->select()) {


            $return['status'] = 1;
            $return['msg'] = L('PUBLIC_LOAD');//加载成功;
            $return['data'] = $data;
        } else {

            $return['status'] = 0;
            $return['msg'] = L('PUBLIC_NODATA'); //没有更多用户;
        }

        $this->response($return, $this->returnType);
    }


    /**
     * 我送过的 商品 结束的 和正在进行中。
     */
    public  function  mysend(){

        $return = array('status' => 0);

        if ($this->token()) {


            $page = new \Think\Page(1, 10);

            $limit = $page->firstRow . ',' . $page->listRows;

            $where['is_sale'] = array('eq',1);


            $where['uid'] = $this->uid;

            $data = M('goods')->where($where)->order('gid desc')->limit($limit)->select();

            if ($data) {
                $return['status'] = 1;
                $return['data'] =$data;

            } else {

                $return['status'] = 2;
            }

        }
        $this->response($return, $this->returnType);

    }




    /**
     * 修改库存
     */
    public function changeInventory()
    {


        $number=I('number', 1, 'intval')? I('number', 1, 'intval') : 1;// 库存。

        $return = array('status' => 0);

        if ($this->token()) {



            $where['uid'] = $this->uid;

            $where['gid'] = I('gid');

            $save['goodsnumber'] =  $number;
            $save['is_sale'] =1;

            $data = M('goods')->where($where)->save($save);


            if ($data) {
                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED'); //成功

            } else {

                $return['status'] = 2;

                $return['msg'] = L('PUBLIC_FAILED'); //失败
            }

        }
        $this->response($return, $this->returnType);



    }


    


}