<?php
/**
 *  ������ͼģ��
 */
namespace Api\Model;

use LAP\Model\ViewModel;

Class OrdersViewModel extends ViewModel
{
    protected $tableName = 'orders';
    Protected $viewFields = array(
        'orders' => array(
          'oid','uid','time','express','address','gid','money','sendtime','confirmtime','senduid','goodsmodetype','ostate',



            '_type' => 'LEFT'
        ),
        'member' => array(
            'nickname', 'face','email',
            '_on' => 'orders.uid = member.uid'
        )
    );
}

?>