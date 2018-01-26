<?php
namespace Api\Controller;

use LAP\Controller;

/**
 * Class JoinSelfModeController
 * @package Api\Controller
 *
 *  竞拍模式 商品
 *
 */
class joinAuctionModeController extends JoinBaseController
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

    private $UpPriceMax;

    function __construct($goodsInfo, $user)
    {
        parent::__construct($goodsInfo, $user);
        $this->hasJoin();
        //
        $this->checkBalance();
        $this->checkAddress();


        $this->checkUpPriceMax();

        $this->addOrecord();
    }


    /**
     *  检测   商品的属性
     */

    public function hasJoin()
    {
        parent::hasJoin();


    }

    /**
     *  返回地址 ，如果是第一次的话就显示地址 不是的话 就不弹出地址
     */

    protected function checkAddress()
    {
        if (!D('Auctionrecord')->getIsJoin($this->goodsIfo['gid'], $this->user['uid'])) {

            $this->checkTili();
            if (!$this->goodsIfo['address'] && $address = M('deliveryaddress')->where("uid={$this->user['uid']} and is_default=1 ")->find()) {

                $arr['status'] = 12;//存在
                $arr['address'] = $address;//
                $arr['msg'] = '';//
                $this->ajaxReturn($arr);
            } elseif (!$this->goodsIfo['address']) {
                $arr['status'] = 11;//不存在
                $arr['msg'] = '';
                $this->ajaxReturn($arr);

            }

        }
    }

    /**
     * 检查 出价者的LAP( 和 出的币
     */
    private function checkBalance()
    {   

        if ($this->user['balance'] < $this->goodsIfo['userUpPrice']) {
            $retun['msg'] = L('PUBLIC_NOMONEY');//'LAP(不足出价失败';
            $retun['status'] = 2;
            $this->ajaxReturn($retun);
        }

    }

    /**
     * 查询出价人和当前最高的对比 小于 的话 不允许出价
     */
    private function checkUpPriceMax()
    {
        $this->UpPriceMax = $this->goodsIfo['price'];


        if ($this->UpPriceMax >= $this->goodsIfo['userUpPrice']) {
            $retun['msg'] =L('JOINAUCTIONMODE');// '你的出价必须大于最高出价';
            $retun['status'] = 14;
            $this->ajaxReturn($retun);
        }

    }


    /**
     *  添加 记录
     *
     *
     *   把自己的出价 冻结
     *
     */

    private function addOrecord()
    {
        $this->actionSecond($this->goodsIfo['gid']); //对第一名操作



        $sign=encryptString(json_encode(array('address'=>$this->user['blockaddress'],'pubkey'=>$this->user['address_key'],'privkey'=>$this->key)));

        if (D('Auctionrecord')->addRecord($this->goodsIfo, $this->user['uid'], $this->UpPriceMax,$sign)) {


            $retun['msg'] = L('PUBLIC_SUCCEED');//'竞拍成功';
            $retun['status'] = 6;

            $this->addGsellnums();

            $this->upDateUpPriceMax($this->goodsIfo['gid'], $this->goodsIfo['userUpPrice']);// 更新最大竞拍价格


            $this->frozenBlance($this->user['uid'], '-' . $this->goodsIfo['userUpPrice']);  // 冻结自己的资产


            if ($this->goodsIfo['endtime'] - time() < 300) {
                $this->updateEndTime($this->goodsIfo['gid']);
            }


        } else {
            $retun['msg'] = L('PUBLIC_FAILED');//'竞拍失败';
            $retun['status'] = 7;
        }


        $this->ajaxReturn($retun);
    }

    /**
     * 给第二名发推送
     *
     * 解冻 当前的 资产
     *
     *
     */
    private function actionSecond($gid)
    {




        $auction_record = M('auction_record')->where("gid=$gid")->order('money desc')->find();



        if($auction_record){

        $this->frozenBlance($auction_record['uid'], $auction_record['money']);  // 解冻资产   上一个资产


        $save['unfrozen_time'] = time();
        $save['is_frozen'] = 0;
        $save['id'] =$auction_record['id'];
        $save['sign'] ='';

         M('auction_record')->save($save);










        if ($auction_record['uid'] != $this->user['uid']) {

            $array['uid']=$auction_record['uid'];
            $array['gid']=$auction_record['gid'];
            $array['type']=4;
            $array['content']=sprintf(L('PUSH_3'),$this->goodsIfo['title']);
            push($array);
        }

        }
    }

    /**
     * @param $gid
     * @param $max
     * 更新最大价格
     */

    private function upDateUpPriceMax($gid, $max)
    {

      M('goods')->where("gid=$gid")->setField('price', $max);
    }


    protected function decScore()
    {
    }


    /*
     * 没出价一次  更新下架时间  +300秒
     *
     */

    private function updateEndTime($gid)
    {
        M('goods')->where("gid=$gid")->setInc('down_time', 300);
    }
}