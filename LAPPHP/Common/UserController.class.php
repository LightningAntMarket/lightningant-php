<?php
namespace Android\Controller;

/**
 * Class UserController
 * @package Android\Controller
 *
 *
 * 用户中心
 */
class UserController extends CommonController
{


    public function index()
    {
        $return = array('status' => 0);

        if ($this->token()) {


            //查询有没有时间交易的商品
            $where['uid'] = $this->uid;
            $where['is_sale'] = 1;

            $time_service_status=M('time_exchange')->where($where)->find();

            unset($this->user['privkey']);
            $read = no_read($this->uid);

            $this->user['time_service_status']=$time_service_status?true:false;

            $return['sumpower']=strval(user_integral('','',end_times()));

            $return['mypower']=strval(user_integral($this->uid,'',end_times()));

            $return['sumlap']=strval(3287.6712328767+S('fee_money'));


            if(!user_integral($this->uid,12,end_times())){
                if($this->user['identification']==3){
                    integral_add($this->uid,12, 5,'KYC认证'); //算力
                }
            }



            $return['data'] = $this->user;
            $return['read'] = $read;

            $return['status'] = 1;

        }
        $this->response($return, $this->returnType);
    }

    /**
     *
     * 修改基本信息
     */
    public function editInfo()
    {


        $return = array('status' => 0);

        $sex = I('sex'); //性别
        $face = I('face');
        $nickname = I('nickname');
        $city = I('city');
        $introduction = I('introduction');

        $password = I('password');


        $sex && $save['sex'] = $sex;
        $face && $save['face'] = $face;
        $city && $save['city'] = $city;
        $nickname && $save['nickname'] = $nickname;
        $introduction && $save['introduction'] = $introduction;
        $password && $save['password'] = md5($password);


        if ($this->token()) {

            $where['uid'] = $this->uid;

            $password && $where['password'] = I('oldpassword', '', 'md5');


            if ($nickname && M('member')->where("nickname='{$nickname}'")->find()) {


                $return = array('status' => 3, 'msg' => L('REGISTER_1'));//'success'


            } else if ($save && M('member')->where($where)->save($save)) {


                $return = array('status' => 1, 'msg' => L('PUBLIC_SUCCEED'));//'success'
            } else {


                $return = array('status' => 2, 'msg' => L('PUBLIC_FAILED'));//'error'
            }
        }


        $this->response($return, $this->returnType);

    }

    /**
     * 护照上传
     */
    public function authentication()
    {
        $return = array('status' => 0);


        $image = I('image');
        $name = I('name','', 'htmlspecialchars');
        $number = I('port_number', '', 'htmlspecialchars');

        if ($this->token()) {
            if ($this->user['identification'] == 1) {
                $return['status'] = 3;
                $return['msg'] = L('USER_0');//认证中

                $this->response($return, $this->returnType);
            }

            if ($this->user['identification'] == 3) {
                $return['status'] = 4;
                $return['msg'] = L('USER_1');//认证成功

                $this->response($return, $this->returnType);
            }

            if($number){
                if (M('member_info')->where("port_number='$number'")->find()) {
                    $return['status'] = 5;
                    $return['msg'] = '证件号码已存在';//认证成功

                    $this->response($return, $this->returnType);
                }
            }
            
            $save['uid'] = $this->uid;
            $save['identification'] = 1;

            $add['uid'] = $this->uid;
            $add['image'] = $image;
            $add['name'] = $name;
            $add['port_number'] = $number;
            $add['uptime'] = time();

            if (M('member')->save($save)) {

                M('member_info')->add($add);
                $return = array('status' => 1, 'msg' => L('PUBLIC_SUCCEED'));
            } else {


                $return = array('status' => 2, 'msg' => L('PUBLIC_FAILED'));
            }
        }


        $this->response($return, $this->returnType);

    }


    /**
     * 根据UID获取头像和昵称。
     */
    public function getFaceByUid()
    {

        $return = array('status' => 0, 'msg' => L('PUBLIC_NODATA'));
        $uid = I('uid', 0, 'intval'); //uid


        $info = M('member')->field('uid,nickname,face,email,blockaddress')->find($uid);


        if ($info) {

            $return['status'] = 1;
            $return['data'] = $info;

        }
        $this->response($return, $this->returnType);

    }

    /**
     *  获取新的区块链 地址
     */

    public function getBlockAddress()
    {
        $return = array('status' => 0, 'msg' => L('USER_GETBLOCKADDRESS_0'));//'已经绑定'
        if ($this->token() && !$this->user['blockaddress']) {


            $getBlockNewAddress = $this->getBlockNewAddress();

            $data['address_key'] = $getBlockNewAddress['address_info']['pubkey'];
            $data['blockaddress'] = $getBlockNewAddress['address_info']['address'];
            $data['privkey'] = hash('sha1', $getBlockNewAddress['address_info']['privkey']);


            $data['uid'] = $this->uid;


            $address = M('blockaddress');


            $address->startTrans();


            $member = M('member');

            $member->startTrans();


            $result = $member->save($data);


            $result1 = $address->add($data);

            if ($data['address_key'] && $result && $result1) {

                $member->commit();
                $address->commit();

                $return = array('status' => 1, 'msg' => L('PUBLIC_SUCCEED'), 'data' => array('privkey' => $getBlockNewAddress['address_info']['privkey']));
            } else {
                $member->rollback();
                $address->rollback();

                $return = array('status' => 2, 'msg' => L('PUBLIC_FAILED'));
            }
        }


        $this->response($return, $this->returnType);
    }

    public function getBlockAddress1()
    {

        $this->token();

        var_dump($this->user);
        $result = $this->getBlockNewAddress();

        var_dump($result);
    }

    /**
     *  检测key是否正确
     */
    public function checkKey()
    {

        $return = array('status' => 0, 'msg' => L('PUBLIC_FAILED'));
        if ($this->token() && $this->user['blockaddress']) {


            $key = I('privkey'); //私钥


            $where['privkey'] = hash('sha1', $key);
            $where['uid'] = $this->uid;


            if (M('member')->where($where)->find()) {
                $return = array('status' => 1, 'msg' => L('PUBLIC_SUCCEED'));
            } else {

                $return = array('status' => 2, 'msg' => L('PUBLIC_FAILED'));
            }

        }


        //echo M()->getLastSql();

        $this->response($return, $this->returnType);


    }


    public function setGoogleCode()
    {


        if ($this->token()) {


            $save['uid'] = $this->uid;
            $save['need_google_code'] = 1;
            M('member')->save($save);
        }
    }

    public function checkCard()
    {


        $realName = I('name');
        $cardNumber = I('port_number');




        $url = "http://interact.ccdi.gov.cn/bbs/bbsIdentity.do";
        //http://interact.ccdi.gov.cn/bbs/bbsIdentity.do

        $post_data = array("realName" => $realName, "idNumber" => $cardNumber);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //post

        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);//返回数据 根据他 判读
        curl_close($ch);

        $data['status'] = 0;
        $data['msg'] = '证件号不符合';
        if ($output == 'ok') {

            $data['status'] = 1;
            $data['msg'] = 'success';
        }


        $this->ajaxReturn($data);


    }

    /**
     *  获取经纬度
     */
    public  function   coordinate(){


        if ($this->token()) {

            $save['longitude']= I('longitude',1,'floatval');//经度
            $save['latitude']= I('latitude',1,'floatval');//纬度

            $save['uid'] = $this->uid;

            M('member')->save($save);
        }

    }

    /**
     * 身份信息
     */
    public function usertype(){
        $uid=I('uid');
        $type=M('member')->where("uid=$uid")->getField('type');

        if($type==20){
            $data['status'] = 1; //商家
        }elseif ($type==10){
            $data['status'] = 2; //用户
        }elseif ($type==40){
            $data['status'] = 2; //用户
        }

        $this->ajaxReturn($data);
    }
}