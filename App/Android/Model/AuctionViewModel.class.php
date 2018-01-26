<?php

namespace Api\Model;

use LAP\Model\ViewModel;

Class AuctionViewModel extends ViewModel
{
    protected $tableName = 'auction_record';
    Protected $viewFields = array(
        'auction_record' => array(
            'time','money',
            '_type' => 'LEFT'
        ),
        'member' => array(
            'nickname','face','uid',
            '_on' => 'auction_record.uid = member.uid'
        )
    );
}

?>