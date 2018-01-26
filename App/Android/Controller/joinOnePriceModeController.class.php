<?php
namespace Api\Controller;

use LAP\Controller;

/**
 * Class joinOnePriceModeController
 * @package Api\Controller
 * 一口价模式 抢购 模式
 * 每次 ostate 1
 */
class joinOnePriceModeController extends JoinBaseController
{

    /**
     * @param $goodsInfo
     * @param $uid  抢购者信息 $user
     * 1。判断数量是否充足
     * 2. 判断是不是已经抢夺‘
     * 3.是不是 需要扣LAP(
     * 4.是不是 有默认地址
     * 5 商品 gsellnum 添加1
     * 6 order 添加1
     *
     */

    function __construct($goodsInfo, $user)
    {
        parent::__construct($goodsInfo, $user);
        $this->hasJoin();
    }

    /**
     *  检测   商品的属性
     */

    public function hasJoin()
    {


        parent::hasJoin();


        $this->checkBalance();  //检测LAP(


        $this->checkAddress();
        $this->addOrder(); //添加订单

    }

    /**
     * 检查LAP( 是否够
     */
    private function checkBalance()
    {

        if ($this->goodsIfo['price'] > $this->user['balance']) {  //判段LAP(够不够

            $retun['msg'] = L('PUBLIC_NOMONEY');//'LAP(不足'
            $retun['status'] = 2;
            $this->ajaxReturn($retun);
        }

    }


    /**
     *  添加 order 记录
     *
     */

    private function addOrder()
    {


       // var_dump($this->user);


        $publickNewAddress = $this->getBlockNewAddress(); //  获取一个公共的区块链地址 一个订单对应一个   每次都需要生成


        $three_pubkeys = array($publickNewAddress['address_info']['pubkey'], $this->user['address_key'], $this->goodsIfo['address_key']);   //公钥 买的人地址 卖人的地址 中间人的地址


        $result=$this->orderSign($three_pubkeys, $this->goodsIfo['price'],$this->user['blockaddress'],$this->key);// 签名



        $address_Key_Array = array('sendBlockAddressPublicKey' => $this->goodsIfo['address_key'], 'getblockAddressPublicKey' => $this->user['address_key'], 'publickNewAddress' => $publickNewAddress['address_info']['address'], 'publickNewkey' => $publickNewAddress['address_info']['pubkey'],


          'privkey'=>$publickNewAddress['address_info']['privkey'],  'sendBlockAddress'=> $this->goodsIfo['blockaddress'], 'getblockAddress'=>$this->user['blockaddress']

        );


        $signArray=array_merge($result,$address_Key_Array);


        $signJson = json_encode($signArray);



        if ($result['txid'] && D('Order')->addOrder($this->goodsIfo, $this->user['uid'],1,$signJson)) {


            M('member')->where("uid={$this->user['uid']}")->setInc('joins', 1);

            $retun['msg'] = L('PUBLIC_SUCCEED');//'购买成功'
            $retun['status'] = 6;

            $this->addGsellnums();
            $this->decScore();
            $this->push();

        } else {
            $retun['msg'] =L('PUBLIC_FAILED') ;//'参加失败'
            $retun['status'] = 7;
        }
        $this->ajaxReturn($retun);
    }

    /*
    *  扣除LAP(
     *
    */
    protected function decScore()
    {
        $this->addBlance($this->user['uid'], '-' . $this->goodsIfo['price'], 1, $this->goodsIfo['gid'], "抢购{$this->goodsIfo['gname']}支出");

    }

    /*
     *  修改 gsellnums 和 goodsum 因为卖一件就减少一件
     *  添 商品的抢夺人数
     */
    protected function addGsellnums()
    {

        $save['hasjoin'] = array('exp', 'hasjoin+1');
        $save['goodsnumber'] = array('exp', ' 	goodsnumber-1');

        if ($this->goodsIfo['goodsnumber'] == 1) { // 当是最后一个商品的时候 商品下架

            $save['is_sale'] = 0;
            $save['down_time'] = time();
        }

        M('goods')->where("gid={$this->goodsIfo['gid']}")->save($save);
    }

    /**
     * 推送消息
     */
    protected function push()
    {


        $array['cid'] = $this->user['cid'];
        $array['uid'] = $this->user['uid'];;
        $array['gid'] = $this->goodsIfo['gid'];
        $array['type'] = 2;
        $array['content'] = sprintf(L('PUSH_2'), $this->goodsIfo['title']);
        push($array);

        $senduser=M('member')->field('cid')->where("uid={$this->goodsIfo['uid']}")->find();
        $arr['cid'] = $senduser['cid'];
        $arr['uid'] = $this->goodsIfo['uid'];
        $arr['gid'] = $this->goodsIfo['gid'];
        $arr['type'] = 10;
        $arr['content'] = sprintf(L('PUSH_8'), $this->goodsIfo['title']);
        push($arr);
    }


}