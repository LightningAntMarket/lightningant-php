<?php
namespace Api\Controller;

/**
 * Class JoinBaseController
 * @package Api\Controller
 * 我要抢 基类
 * 以下流程
 *
 * 1。判断数量是否充足
 * 2. 判断是不是已经抢夺‘
 * 3.是不是 需要扣LAP(
 * 4.是不是 有默认地址
 * 5 商品 gsellnum 添加1
 * 6 order 添加1
 *
 */

abstract class JoinBaseController extends CommonController
{
    public $goodsIfo;
    public $user;

    /**
     * @param $goodsInfo
     * @param $user  抢购者信息  $user
     */

    function __construct($goodsInfo, $user)
    {
        parent::__construct();
        $this->goodsIfo = $goodsInfo;
        $this->user = $user;
    }


    /**
     * 扣除币
     */
    abstract protected function  decScore();


    /**
     * 检查参加资格
     *
     * @param $uid  参加者uid
     *
     */
    protected function  hasJoin()
    {
        if ($this->goodsIfo['uid'] == $this->user['uid']) {  //抢购者等于发布者
            $retun['msg'] = L('JOINBASE_0');//'自己不能抢购自己的';
            $retun['status'] = 1;
            $this->ajaxReturn($retun);
        }

        if ($this->goodsIfo['new'] && D('Order')->isNewUser($this->user['uid'])) {  //新品
            $retun['msg'] = '此商品只限新手抢';
            $retun['status'] = 3;
            $this->ajaxReturn($retun);
        }


        if ($this->goodsIfo['renzheng'] && $this->user['typeid'] < 9) {  //认证可抢
            $retun['msg'] = '仅限认证用户可抢';
            $retun['status'] = 9;
            $this->ajaxReturn($retun);

        }
        if ($this->goodsIfo['onlyonce'] && D('Order')->joinOnce($this->goodsIfo['onlyonce'], $this->user['uid'])) {  //现在一次


            $retun['msg'] = '次商品只能抢购一次';
            $retun['status'] = 4;
            $this->ajaxReturn($retun);

        }


    }

    /**
     *  添 商品的抢夺人数
     */
    protected function  addGsellnums()
    {
        M('goods')->where("gid={$this->goodsIfo['gid']}")->setInc('hasjoin', 1);
    }


    protected function  checkAddress()
    {

        if (!$this->goodsIfo['address']&&$address =M('deliveryaddress')->where("uid={$this->user['uid']} and is_default=1 ")->find()) {

            $arr['status'] = 12;//存在
            $arr['address'] = $address;//
            $arr['msg'] = '';//
            $this->ajaxReturn($arr);
        } elseif(!$this->goodsIfo['address']) {
            $arr['status'] = 11;//不存在
            $arr['msg'] = '';
            $this->ajaxReturn($arr);

        }

    }
}