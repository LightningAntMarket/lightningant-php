<?php
namespace Api\Model;

use LAP\Model;

/**
 * Class FeedbackModel
 * @package Api\Model
 * @ 竞拍模型
 */
class AuctionrecordModel extends Model
{
    protected $tableName   ='auction_record';

    static function  getObj()
    {
        return M('auction_record');
    }


    public function  addRecord($goods, $uid, $max,$sign)
    {


        $data['uid'] = $uid;
        $data['time'] = time();
        $data['gid'] = $goods['gid'];
        $data['money'] = $goods['userUpPrice']; // 加的价格
        $data['address'] = $goods['address'];
        $data['bided'] = $goods['userUpPrice']; //最终将
        $data['sign'] = $sign; //最终将
        if (self::getObj()->add($data)) {

            return true;

        } else {

            return false;
        }
    }

    /**
     * 获取本商品当前最大的起拍价
     */
    public function getMaxMoney($gid)
    {
        return self::getObj()->where("gid={$gid}")->max('bided');

    }

    /**
     * @param $gid
     * @param $uid
     * @return mixed
     * 判断是 不是 已经参加
     */
    public function  getIsJoin($gid, $uid)
    {
        return self::getObj()->where("gid={$gid} and uid={$uid} ")->count();
    }

    /**
     * 获取当前最高 出价人
     */
    public function getTop($gid)
    {
        return self::getObj()->where("gid={$gid}")->order('money desc')->find();
    }
}



