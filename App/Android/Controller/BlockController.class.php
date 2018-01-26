<?php
namespace Api\Controller;


class BlockController extends CommonController
{


    protected $const_permission_names = array(
        'connect' => 'Connect',
        'send' => 'Send',
        'receive' => 'Receive',
        'issue' => 'Issue',
        'mine' => 'Mine',
        'admin' => 'Admin',
    );


    /**
     * 转币
     */
    public function send()
    {


        $return = array('status' => 0, 'msg' => 'user error');


        $to = I('to');

        $note = I('note');//备注

        $key = I('privkey');
        $number = I('number', 0, 'floatval');

        if ($this->token() && $to && $number > 0) {


            if (hash('sha1', $key) != $this->user['privkey']) {
                $return['status'] = 15;
                $return['msg'] = L('JOIN_0');//'私钥不对';

                $this->response($return, $this->returnType);

            }

            if ($this->user['google_code'] && $this->user['need_google_code']) {
                $return['status'] = 16;
                $return['msg'] = L('JOIN_1');//'请先进行谷歌验证';
                $this->response($return, $this->returnType);

            }


            if ($this->user['balance'] < $number) {
                $return['status'] = 17;
                $return['msg'] = L('PUBLIC_NOMONEY');//'no money';//账户余额
                $this->response($return, $this->returnType);

            }


            $real_get = $number * (1 - C('FEE'));


            $sendArray = array($to => array('LAP' => $real_get));

            $where['blockaddress'] = $to;

            $member = M('member')->field("cid,uid,nickname")->where($where)->find();


            $bin2hex_uid = bin2hex($member['uid']);


            $bin2hex_note = bin2hex($note);


            $this->no_displayed_error_result($hex, $this->LAP('createrawsendfrom', $this->user['blockaddress'], $sendArray, array($bin2hex_uid, $bin2hex_note)));

            $this->no_displayed_error_result($result, $this->LAP('signrawtransaction', $hex, array(), array($key)));//签名

            $this->no_displayed_error_result($result, $this->LAP('sendrawtransaction', $result['hex']));//sendrawtransaction//发布到网络


            $this->fee($this->user['blockaddress'], $number, $key);


            if ($result) {

                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED');

            } else {

                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_FAILED');//'not ok';
            }

        }

        $this->response($return, $this->returnType);
        // var_dump($sendtxid);


    }


    /**
     * 获取转账列表
     */
    public function getLog()
    {


        $return = array('status' => 0, 'msg' => 'user error');

        $p = I('p', 0, 'intval');//分页

        if ($this->token()) {

            $success = $this->no_displayed_error_result($result, $this->LAP('listaddresstransactions',
                $this->user['blockaddress'], 10, $p * 10));


            if ($result) {

                $array = array();

                foreach ($result as $key => $value) {


                    if ($value['balance']['assets'][0]['qty']) {

                        $array[$key]['qty'] = $value['balance']['assets'][0]['qty'];
                        $array[$key]['myaddresses'] = $value['myaddresses'];
                        $array[$key]['addresses'] = $value['addresses'];
                        $array[$key]['txid'] = $value['txid'];
                        $array[$key]['time'] = $value['time'];
                        $array[$key]['data'] = $value['data'];
                        $array[$key]['timereceived'] = $value['timereceived'];
                    }
                }

            }


            if ($array) {
                $return['data'] = array_reverse($array);
                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED');//'ok';

            } else {
                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');//'no more data';暂无数据

            }


        }

        $this->response($return, $this->returnType);

    }

    /**
     * 详情
     */
    public function details()
    {
        $return = array('status' => 0, 'msg' => 'user error');
        $txid = I('txid');

        if ($this->token()) {

            $success = $this->no_displayed_error_result($result, $this->LAP('getaddresstransaction', $this->user['blockaddress'], $txid));

            if ($result) {

                $array = array();

                if ($result['balance']['assets'][0]['qty']) {

                    $array['qty'] = $result['balance']['assets'][0]['qty'];
                    $array['myaddresses'] = $result['myaddresses'][0];
                    $array['addresses'] = $result['addresses'][0];
                    $array['txid'] = $result['txid'];
                    $array['time'] = $result['time'];
                    $array['timereceived'] = $result['timereceived'];
                    $array['data'] = $result['data'];
                }

                $return['data'] = $array;
                $return['status'] = 1;
                $return['msg'] = L('PUBLIC_SUCCEED');//'ok';
            } else {
                $return['status'] = 2;
                $return['msg'] = L('PUBLIC_NODATA');//'no more data';

            }
        }

        $this->response($return, $this->returnType);
    }
}