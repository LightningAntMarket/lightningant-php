<?php
namespace Api\Model;

use LAP\Model;

/**
 * Class FeedbackModel
 * @package Api\Model
 * @ 订单模型
 */
class OrderModel extends Model
{
    protected $tableName   ='orders';
    /**
     * @param $oid 订单号
     * @param $express  快递信息  express 包含 物流公司 快递单号
     */


    static function  getObj()
    {
        return M('orders');
    }

    public function  changeExpress($oid, $express)
    {
        if ($oid && self::getObj()->where("oid=$oid")->setField('express', $express)) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * @param $oid 订单号
     * @return mixed  根据订单号返回 gid
     */
    function  getGid($oid)
    {


        $gid = self::getObj()->where("oid=$oid")->getField('gid');


        if ($gid) {

            return $gid;
        }

    }

    /**\
     * @param $uid
     * @param $gid
     * 是否参加
     */
    public function isJoin($uid, $gid)
    {

        if (M('orders')->where("uid={$uid} and gid={$gid}")->find()) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * 修改订单状态
     */

    public function  changeOstate($oid, $ostate)
    {

        $time = time();

        switch ($ostate) {
            case 6:
                $data['ostate'] = 6;
                $data['fahuoshijian'] = $time;

                break;
            case 8:
                $data['ostate'] = 8;
                $data['shouhuotime'] = $time;
                $data['showorder'] =0;//变成 已经晒单

                break;
        }

        if (self::getObj()->where("oid=$oid ")->save($data)) {
            return 1;
        } else {
            return 0;
        }

    }


    /**
     * 查询  某状态下的数量
     * @param $gid
     * @param $ostate
     * @param string $type 状态
     * @return mixed
     */

    public function countNum($gid, $ostate, $type = '=')
    {
        return self::getObj()->where("gid={$gid} and ostate {$type} {$ostate}")->count();
    }

    /**
     * @param $oid 订单号
     * @param $uid  用户id
     * @return mixed  根据订单号返回 gid
     */
    function  getGidByOidUid($oid, $uid)
    {


        $gid = self::getObj()->where("oid={$oid} and uid={$uid}")->getField('gid');


        if ($gid) {

            return $gid;
        }

    }

    /**
     * @param $uid
     */
    public function  isNewUser($uid)
    {
        if (self::getObj()->where("ostate in (1,3,4,6,8)and uid={$uid}")->find()) {

            return true;
        }
    }

    /**
     * 判读只能参加一次的商品 是否参加了
     */

    public function  joinOnce($onlyonce, $uid)
    {
        if (self::getObj()->where(array('uid' => $uid, 'onlyonce' => $onlyonce))->find()) {

            return true;
        }
    }

    public function  addOrder($goods, $uid,$state=0,$signJson)
    {
//        $data['uid'] = $uid;
//        $data['otime'] = time();
//        $data['ostate'] =$state;
//        $data['gid'] = $goods['gid'];
//        $data['senduid'] = $goods['uid'];
//        $data['goodsmodetype'] = $goods['modetype'];
//        $data['score'] = $goods['goldprice'];
//        $data['posttype'] = $goods['postype'];
//        $data['postprice'] = $goods['postprice'];
//        $data['onlyonce'] = $goods['onlyonce'] ? $goods['onlyonce'] : 0;
//        $data['address'] = $goods['addid'];
//        $data['overtime'] = time();
//

        $order['time']=time();
        $order['uid']=$uid;
        $order['address']= $goods['address'];
        $order['gid']= $goods['gid'];
        $order['goodsmodetype']=  $goods['modetype'];
        $order['senduid']=  $goods['uid'];
        $order['money']=  $goods['price'];
        $order['is_winning']=  $state;


        $order['sign']=$signJson;

        if ($oid=self::getObj()->add($order)) {


            return $oid;
        } else {

            return false;
        }
    }


    public function  getJoiners($gid)
    {

        return self::getObj()->where("gid=$gid")->getField('uid', true);
    }


    /**
     * 查询  某状态下的数量
     * @param $gid
     * @param $ostate
     * @param string $type 状态
     * @return mixed
     */

    public function getOrders($gid, $ostate, $type = '=')
    {
        return self::getObj()->where("gid={$gid} and ostate {$type} {$ostate}")->select();
    }


    /**
     *
     * 修改订单状态
     */


    /**
     * @param $uid
     * @param $gid
     */
    function  changeOstateByUidAndGid($uid,$gid,$ostate)
    {

        $data['overtime'] = time();
        $data['ostate'] = $ostate;
        self::getObj()->where("gid={$gid} and uid in ( {$uid} ) ")->save($data);
    }
}



