<?php
namespace Android\Controller;


class IndexController extends CommonController
{

    protected $allowMethod = array('get', 'post', 'put'); // REST允许的请求类型列表

    protected $allowType = array('html', 'xml', 'json'); // REST允许请求的资源类型列表


    public function index()
    {

        echo 'success';
    }

    public function banner()
    {


        $return = array('status' => 2, 'msg' => L('PUBLIC_NODATA'));

        $where['cid'] = I('cid');

        $where['isshow'] = 1;

        $where['classify'] = 1;

        $order = 'sort desc';

        $field = 'title,picpatch,jumpurl,cid,gid,type';

        $data = M('image')->field($field)->where($where)->order($order)->select();
        $guadan1=M('entrust')->where("type=1 and status=10")->order('lcny_price desc')->find(); //买入

        $guadan2=M('entrust')->where("type=2 and status=10")->order('lcny_price asc')->find(); //卖出
        $guadan = array();
        isset($guadan1) ? array_push($guadan,$guadan1) : "";
        isset($guadan2) ? array_push($guadan,$guadan2) : "";
        if ($data||$guadan) {
            $return['data'] = $data;
            $return['guadan'] = $guadan;
            $return['status'] = 1;
            $return['msg'] = L('PUBLIC_LOAD');
        }

        $this->response($return, $this->returnType);

    }

    /**
     * 首页商品列表
     */
    public function goods_list()
    {

        $return = array('status' => 2, 'msg' => L('PUBLIC_NODATA'));

        $page = new \Think\Page(1, 30);
        $time = time();
        $limit = $page->firstRow . ',' . $page->listRows;
        $where['is_sale'] = 1;
        $where['up_time'] = array("lt",$time);
        $where['down_time'] = array("gt",$time);
        if ($_SERVER['HTTP_VERSION']=='1.4.5')
        {
            $where['modetype'] = array("neq",3) ;
        }
        $model = M('goods');
        $goods_top = $model->where($where,['top'=>1])->order('top desc , top_expire desc')->limit($limit)->select();
        //判断置顶时间
        if($goods_top)
        {
            foreach ($goods_top as $v)
            {
                if ($v['top_expire']<time())
                {
                    //置顶已经过期
                    $save_top = $model->where(['gid'=>$v['gid']])->save(['top'=>0]);
                }
            }
        }
        $goods = $model->where($where)->order('top desc ,top_expire desc ')->limit($limit)->select();
        //分组
        $top = array();
        $no_top = array();
        foreach ($goods as $v)
        {
            if ($v['top'] == 1 )
            {
                $top[] = $v;
            }
            else
            {
                $no_top[] = $v;
            }
        }
        //随机排序
        shuffle($no_top);

        if (array_merge($top,$no_top)) {
            $return['data'] = array_merge($top,$no_top);
            $return['msg'] = L('PUBLIC_LOAD');
            $return['status'] = 1;
        }

        $this->response($return, $this->returnType);


    }


    /**
     * C2C委托订单  最高买入和最低卖出 两条
     */
    public function entrustorders(){

        $return = array('status' => 2, 'msg' => L('PUBLIC_NODATA'));

        $data[0]=M('entrust')->where("type=1 and status=10")->order('lcny_price asc')->find(); //买入

        $data[1]=M('entrust')->where("type=2 and status=10")->order('lcny_price desc')->find(); //卖出
        
        if ($data) {
            $return['data'] = $data;
            $return['status'] = 1;
            $return['msg'] = L('PUBLIC_LOAD');
        }


        $this->response($return, $this->returnType);

    }

}