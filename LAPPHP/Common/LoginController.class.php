<?php
namespace Android\Controller;

/**
 * Class LoginController
 * @package Android\Controller
 * 登录借口
 */
class LoginController extends CommonController
{


    public function index()
    {


        $where['password'] = I('post.password');
        $where['email'] = I('post.email');
     $cid = I('post.cid');

        $login = D('Login');


        if ($login->create($where)) {


            if ($user = M('member')->where($where)->find()) {


                $token = jiami("email/{$user['email']}/password/{$user['password']}");

                $svae['tokens'] = md5($token);

                $svae['logintime'] = time();

                $svae['uid'] = $user['uid'];

                $svae['cid'] = $cid;// 推送号

                $svae['last_login_ip']=get_client_ip();
                $svae['imei']=$_SERVER['HTTP_IMEI']?$_SERVER['HTTP_IMEI']:I('post.uuid');
                M('member')->save($svae);


                $log['uid']=$user['uid'];
                $log['time']=time();
                $log['email']=$user['email'];
                $log['ip']=get_client_ip();
                $log['imei']=$_SERVER['HTTP_IMEI']?$_SERVER['HTTP_IMEI']:I('post.uuid');
                M('login_log')->add($log);

                
                $return['msg'] = 'success';
                
                $return['status'] = 1;

                $return['uid'] = $svae['uid'];

                $return['tokens'] = $token;

                $return['google_code'] =$user['google_code'];
                $return['face'] =$user['face'];
                $return['nickname'] =$user['nickname'];

            }else{
                $return['msg'] =L('LOGIN_0');
                $return['status'] = 0;


            }

        } else {

            $return['msg'] = $login->getError();
            $return['status'] = 0;

        }

        $this->response($return, $this->returnType);


    }


}