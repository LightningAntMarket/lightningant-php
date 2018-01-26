<?php
namespace Api\Controller;


/**
 * Class JoinController
 * @package Api\Controller
 * 抢夺总入口
 */
class JoinController extends CommonController
{

//    protected $goods; //
//    protected $address; //
//
//
//    /**
//     *
//     * 获取商品的类型 根据不同的  modetype 来调去不同的数据
//     */
//
//
//    private function  getGoodsType($goodsInfo)
//    {
//
//
//        $goodsInfo['gid'] && $GoodsExtends = M('goods_extends')->where("egid={$goodsInfo['gid']}")->find();
//
//        $GoodsExtends&& $goodsInfo = array_merge($GoodsExtends, $goodsInfo);
//
//
//        FactoryController::JoinMode($goodsInfo, $this->user);//调用工厂函数
//
//    }
//
//    function  index()
//    {
//
//
//        $gid = I('get.gid', '', 'intval');
//
//        $goods = D('Goods');
//
//        $addid = I('get.addid', '', 'intval');
//
//        $goodsInfo = $goods->getOneGoodsInfo($gid);
//
//
//        $goodsInfo['userUpPrice']= I('get.userUpPrice', '', 'intval'); //用户出的价格
//
//        if ($goodsInfo['issale']==1) { //可以参加抢夺
//
//
//            $goodsInfo['addid'] = $addid;
//            $this->token() ? $this->getGoodsType($goodsInfo) : die('not your self');
//
//
//        }
//
//
//    }


    public function index()
    {


        $return = array('status' => 0, 'msg' => L('_USER_ERROR_'));

        if ($this->token()) {


            $gid = I('gid', 0, 'intval');// 商品的id


            $this->key = I('privkey');//抢的人的私钥

            $where['gid'] = $gid;

            $where['is_sale'] = 1;

            $goods = M('goods')->where($where)->find();


//            var_dump(I());





            if (hash('sha1', $this->key) != $this->user['privkey']) {
                $return['status'] = 15;
                $return['msg'] = L('JOIN_0');//私钥错误

                $this->response($return, $this->returnType);

            }

            if ($this->user['google_code']&&$this->user['need_google_code']) {
                $return['status'] = 16;
                $return['msg'] = L('JOIN_1');//'请先进行谷歌验证'
                $this->response($return, $this->returnType);

            }


            if ($goods) {//对比key  并且不需要谷歌验证码
                I('city') && I('address') && I('consignee') && I('phone') && $goods['address'] = I('city') . ',' . I('address') . ',' . I('consignee') . ',' . I('phone');


                $goods['userUpPrice'] = I('userUpPrice'); //用户出的价格


                $send_member = M('member')->field('address_key,blockaddress')->find($goods['uid']);


                $goods['address_key'] = $send_member['address_key'];

                $goods['blockaddress'] = $send_member['blockaddress'];


                switch ($goods['modetype']) {
                    case 1: // 随机模式
                        new  joinRandomModeController($goods, $this->user);
                        break;
                    case 2://yikoujia
                        new joinOnePriceModeController($goods, $this->user);
                        break;
                    case 3: //竞拍

                        new joinAuctionModeController($goods, $this->user);
                        break;

                }


            } else {

                $return['status'] = 0;
                $return['msg'] = L('_GOODS_DETAIL_0');//'商品不存在已经下架';
            }
        }


        $this->response($return, $this->returnType);

    }

}