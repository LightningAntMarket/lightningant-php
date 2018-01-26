<?php

namespace  Api\Model;

use LAP\Model\ViewModel;

Class OrderSendViewModel extends ViewModel
{


    Protected $viewFields = array(


        'goods' => array(
            'cover','description','title',
            '_type' => 'LEFT',
        ),


        'orders' => array(
           '*','uid'=>'ouid',
            '_type' => 'LEFT',
            '_on' => 'orders.gid = goods.gid',

        ),

        'member' => array(
            'nickname','face','uid',
            '_on' => 'orders.uid = member.uid'

        )
    );
}

?>