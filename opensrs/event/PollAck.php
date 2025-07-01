<?php

namespace opensrs\event;

use opensrs\Base;

class PollAck extends Base
{
    public $action = 'ack';
    public $object = 'event';

    public $_formatHolder = '';
    public $resultFullRaw;
    public $resultRaw;
    public $resultFullFormatted;
    public $resultFormatted;
    
    public $requiredFields = array(
        'attributes' => array(
            'event_id'
        )
    );

    public function __construct($formatString, $dataObject, $returnFullResponse = true)
    {
        parent::__construct();

        $this->_formatHolder = $formatString;
        $this->_validateObject($dataObject);

        $this->send($dataObject, $returnFullResponse);
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
