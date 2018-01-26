<?php
namespace Api\Controller;

use LAP\Controller\RestController;


/**
 * Class CommonController
 * @package Api\Controller
 * common 控制器
 */
class CommonController extends RestController
{

    protected $allowMethod = array('get', 'post', 'put'); // REST允许的请求类型列表

    protected $allowType = array('html', 'xml', 'json'); // REST允许请求的资源类型列表


    protected $returnType = 'json';


    protected $user;
    protected $uid;

    /**
     * @return bool 验证token 方法
     */
    protected function token(){}





    private function json_rpc_send($method = 'getinfo', $params = array())
    {
        $name = 'LightingAnt';
        $rpchost = '';
        $rpcport = '';
        $rpcuser = '';
        $rpcpassword = '';


        $url = 'http://' . $rpchost . ':' . $rpcport . '/';

        $payload = json_encode(array(
            'id' => time(),
            'method' => $method,
            'params' => $params,
        ));


        $ch = curl_init($url);


        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $rpcuser . ':' . $rpcpassword);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload)
        ));


        $response = curl_exec($ch);


        $result = json_decode($response, true);

        if (!is_array($result)) {
            $info = curl_getinfo($ch);
            $result = array('error' => array(
                'code' => 'HTTP ' . $info['http_code'],
                'message' => strip_tags($response) . ' ' . $url
            ));
        }

        return $result;
    }

    protected function no_displayed_error_result(&$result, $response)
    {
        if (is_array($response['error'])) {
            $result = null;
            $this->output_rpc_error($response['error']);
            return false;

        } else {
            $result = $response['result'];
            return true;
        }
    }


    /**
     *
     * 生成新的区块链地址
     *
     * 然后导入
     */

    protected function getBlockNewAddress()
    {


        $this->no_displayed_error_result($return, $this->LAP('createkeypairs', 1));//创建   DIZHI


        $result = $this->no_displayed_error_result($txid, $this->LAP('importaddress', $return[0]['address'], '', false));//导入地址


        $grant_result = $this->no_displayed_error_result($txid1, $this->LAP('grant', $return[0]['address'], 'receive,send'));//授权


        $temp = array(

            'address_info' => $return[0],

            'importaddress_result' => $result,


        );

        return $temp;

    }


    /**
     * 订单签名
     */

    protected function orderSign($publickKeys, $moeny, $getAddress, $key)
    {



        $this->no_displayed_error_result($multisigaddress, $this->LAP('addmultisigaddress', 2, $publickKeys));//创建  多签名地址：返回  string(38) "41kf4cPnNyGD3pTqMk4q8tVBUB2UFNXUdjYNBi"


        $importaddress_result = $this->no_displayed_error_result($txid, $this->LAP('importaddress', $multisigaddress, '', false));//导入地址


        $grant_result = $this->no_displayed_error_result($txid, $this->LAP('grant', $multisigaddress, 'receive,send'));//授权


        $temp = array("$multisigaddress" => array('LightingAnt' => floatval($moeny*(1-C('FEE')))));


        $this->no_displayed_error_result($hex, $this->LAP('createrawsendfrom', $getAddress, $temp));//创建一个交易


        $this->no_displayed_error_result($hex_, $this->LAP('signrawtransaction', $hex, array(), array($key)));//签名


        $this->no_displayed_error_result($txid, $this->LAP('sendrawtransaction', $hex_['hex']));//发布到网络


        $this->no_displayed_error_result($addressInfo, $this->LAP('listunspent', 0, 99999, array($multisigaddress)));//获取地址




        $this->fee($getAddress,floatval($moeny),$key);//手续费



        $addressInfo=$addressInfo[0]['assets'][0]?$addressInfo[0]:$addressInfo[1];

        $temp = array(

            'multisigaddress' => $multisigaddress,

            'importaddress_result' => $importaddress_result,

            'grant_result' => $grant_result,

            'addressInfo' => $addressInfo,

            'txid' => $txid,

        );

        return $temp;


    }

    /**
     * 2 of 3 签名
     *
     * 该接口是从第三方（多签名地址） 地址 向外发送资产
     *
     * 如果 ok =1  钱打给 卖家  证明 交易完成了。  $key 是买家私钥
     *
     * 如果 ok=0   钱退给  买家    此时 交易关闭  证明 卖家同意退货物  $key 是卖家私钥
     *
     * $key
     */


    protected function operateBlockBlance($order, $key, $ok = 1)
    {


        $sign = json_decode($order['sign'], true);

        $data = array(

            array('txid' => $sign['addressInfo']['txid'],
                'scriptPubKey' => $sign['addressInfo']['scriptPubKey'],
                'redeemScript' => $sign['addressInfo']['redeemScript'],
                'address' => $sign['addressInfo']['address'],
                'vout' => 0,
            )

        );//第三方公共地址

        $endAddress = $ok ? $sign['sendBlockAddress'] : $sign['getblockAddress'];

        $data1 = array(

            $endAddress => array('LightingAnt' => floatval($order['money']*(1-C('FEE'))))

        );//收货人地址


        if ($endAddress) {

            $this->no_displayed_error_result($hex, $this->LAP('createrawtransaction', $data, $data1));//创建一个交易


            $this->no_displayed_error_result($hex_, $this->LAP('signrawtransaction', $hex, $data, array($sign['privkey'])));//签名1


            $this->no_displayed_error_result($hex_, $this->LAP('signrawtransaction', $hex_['hex'], $data, array($key)));//签名2


            $this->no_displayed_error_result($txid, $this->LAP('sendrawtransaction', $hex_['hex']));//发布到网络







            return $txid;
        }
        //$txid  内容如下
        //["hex"]=>
        //     string(744) "0100000001458a23970429916904e6ae18e56f54e3cfea8ba7378f344738c5663f0c89c84100000000fdff00004830450221009f444b983333b90767fcf51d53322a656fbd940c87a4d15bfe3a373a450dc29b022062987f71ddd32ee3565875d6e4447433f9829a4dc35d256ed0c6e2bae520df2a014830450221009b82f74bb320c3e3755239cea92ca29ca24f31f4c3ab0c499f38a9c603b853b102206f062fc4645b66f8f0f3dbd9595e527590e289d31da0ba9e226df943e73bb90a01004c69532102d785862059753cc3ff737d61eb7416c739233a28039991d952abfa35943575012102464c4d87510c4d9d8e240ea4c486106df368c1ea05c56b90929b2f9d14b4560c2103cd2a74332723183788737a551121e3bd43e72b61d3de7e4803cfd7cb955be0ee53aeffffffff0100000000000000003776a914be4b102c051c41d30c5370cb4e444f44d260595f88ac1c73706b710a5b0fd80417e755dc72100d1e4f0a4c0a000000000000007500000000"
        //   ["complete"]=>
        //bool(false)


    }

    /*
     * $pay_fee_address,  付手续费地址
     * $number,  交易的数量
     *
     * $key  交易的key
     *
     */

     protected function  fee($pay_fee_address,$number,$key){


         $sendArrayFee = array(C('FEE_ADDRESS') => array('LAP' => $number * (C('FEE'))));

         $feeRemark = bin2hex('Translation Fee');

         $this->no_displayed_error_result($hex_fee, $this->LAP('createrawsendfrom',$pay_fee_address , $sendArrayFee, array($feeRemark)));

         $this->no_displayed_error_result($result_fee, $this->LAP('signrawtransaction', $hex_fee, array(), array($key)));//签名

         $this->no_displayed_error_result($txid_fee, $this->LAP('sendrawtransaction', $result_fee['hex']));//sendrawtransaction//发布到网络



     }


    protected function LAP($method) // other params read from func_get_args()
    {


        $args = func_get_args();

        return $this->json_rpc_send($method, array_slice($args, 1));
    }


}