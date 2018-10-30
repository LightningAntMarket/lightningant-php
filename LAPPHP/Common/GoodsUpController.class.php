<?php
namespace Android\Controller;

/**
 * Class GoodsUpController
 * @package Android\Controller
 *
 * 商品s */
class GoodsUpController extends CommonController
{


    public function index()
    {


        $return = array('status' => 0, 'msg' => L('PUBLIC_USERERROR'));//'user error'
        if ($this->token()) {


            $data['modetype'] = I('post.modetype');  //模式
            $data['title'] = I('post.title');
            $data['description'] = I('post.description');
            $data['image'] = I('post.image');
            $temp = explode(',', $data['image']);
            $data['cover'] = $temp[0];

            $data['price'] = I('post.price',1,'floatval');
            $data['commodityaddress'] = I('post.commodityaddress');

            $time=I('post.down_time','259200')*3600;
            $data['down_time'] = time()+($time?$time:933120000);
            $data['up_time'] = time();
            $data['posttype'] = I('post.posttype');
            $data['uid'] = $this->uid;
            $data['goodsnumber'] = I('goodsnumber', 1, 'intval')? I('goodsnumber', 1, 'intval') : 1;

            if(M('guild')->where("uid={$this->uid} and state=1")->find()){

                $data['is_sale'] =1;
            }else{

                $data['is_sale'] =2;

            }


            $data['sendBlockAddress'] =$this->user['blockaddress']; //用哪个钱包地址发的

            $GoodsUp = D('GoodsUp');


            if ($GoodsUp->create($data)) {


                if ($gid = $GoodsUp->add($data)) {

                    $return = array('status' => 1, 'msg' => L('PUBLIC_SUCCEED'), 'gid' => $gid);//'success'

                } else {
                    $return = array('status' => 2, 'msg' =>L('PUBLIC_FAILED') );//'error'
                }

            } else {

                $return['msg'] = $GoodsUp->getError();
                $return['status'] = 3;
            }


        }


        $this->response($return, $this->returnType);

    }


}