<?php

namespace  Api\Model;

use LAP\Model\ViewModel;

Class OrderGetViewModel extends ViewModel
{
    protected $tableName = 'orders';
    Protected $viewFields = array(
        'orders' => array(
           '*','uid'=>'ouid',
            
            '_type' => 'LEFT',

        ),
        'goods' => array(
            'cover','description','title',

            '_on' => 'orders.gid = goods.gid',
            '_type' => 'LEFT',
        ),

        'member' => array(
            'nickname','face','uid','email',
            '_on' => 'goods.uid = member.uid'

        )
    );
}

?>