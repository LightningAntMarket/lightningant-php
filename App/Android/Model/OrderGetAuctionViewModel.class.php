<?php

namespace  Api\Model;

use LAP\Model\ViewModel;

Class OrderGetAuctionViewModel extends ViewModel
{
    protected $tableName = 'auction_record';

    Protected $viewFields = array(

        'auction_record' => array(

           '*','uid'=>'ouid',

            '_type' => 'LEFT',

        ),
        'goods' => array(
            'cover','description','title',

            '_on' => 'auction_record.gid = goods.gid',
            '_type' => 'LEFT',
        ),

        'member' => array(
            'nickname','face','uid','email',
            '_on' => 'goods.uid = member.uid'

        )
    );
}

?>