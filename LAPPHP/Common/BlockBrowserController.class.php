<?php
namespace Android\Controller;


class BlockBrowserController extends CommonController
{


    protected $const_permission_names = array(
        'connect' => 'Connect',
        'send' => 'Send',
        'receive' => 'Receive',
        'issue' => 'Issue',
        'mine' => 'Mine',
        'admin' => 'Admin',
    );


    public function getMyBlance()
    {

        header("Content-type: text/html; charset=utf-8");
        $address = I('get.address');


        $success = $this->no_displayed_error_result($sendtxid, $this->beidouchain('getaddressbalances', $address));

        
        $this->ajaxReturn($sendtxid);

    }


    /**
     * 获取转账列表
     */
    public function getLog()
    {


        header("Content-type: text/html; charset=utf-8");
        $address = I('get.address');

        $success = $this->no_displayed_error_result($result, $this->beidouchain('listaddresstransactions',
            $address, 1000));


        if ($result) {

            $array = array();

            foreach ($result as $key => $value) {

                $str = '';

                if ($value['balance']['assets'][0]['qty']) {
                     $type = "<b style='color:red'>收入</b>";
                    if ($value['balance']['assets'][0]['qty'] < 0) {

                        $type = "<b style='color:green'>支出</b>";
                    }

                    $a="<a href=http://91baisong.com/bs_cn/Android/BlockBrowser/getLog/address/{$value['addresses'][0]}>{$value['addresses'][0]}</a>";



                    $date=date('Y-m-d h:i:s',$value['time']);
                    $str = "时间：{$date},数量：{$value['balance']['assets'][0]['qty']},类型:{$type},相关地址：{$a}.txid:{$value['txid']}<br>";

                }

                echo $str;
            }

        }


    }  public function getLog1()
{


    header("Content-type: text/html; charset=utf-8");
    $address = I('get.address');


    $success = $this->no_displayed_error_result($result, $this->beidouchain('listaddresstransactions',
        $address, 1000));

    if ($result) {

        $array = array();

        foreach ($result as $key => $value) {

            $str = '';

            if ($value['balance']['assets'][0]['qty']) {
              echo    $value['balance']['assets'][0]['qty']."<br>" ;

            }

            echo $str;
        }

    }


}

}