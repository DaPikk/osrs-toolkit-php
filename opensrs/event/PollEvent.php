<?php

namespace opensrs\event;

use opensrs\Base;

class PollEvent extends Base
{
    public $action = 'poll';
    public $object = 'event';

    public $_formatHolder = '';
    public $resultFullRaw;
    public $resultRaw;
    public $resultFullFormatted;
    public $resultFormatted;

    public function __construct($formatString, $dataObject, $returnFullResponse = true)
    {
        parent::__construct();

        $this->_formatHolder = $formatString;
        $this->_validateObject($dataObject);

        $limit = null;
        if (isset($dataObject->data->poll_limit)) {
            $limit = (int)$dataObject->data->poll_limit;
        } elseif (isset($dataObject->poll_limit)) {
            $limit = (int)$dataObject->poll_limit;
        }

        $cmd = array(
            'protocol' => 'XCP',
            'action' => $this->action,
            'object' => $this->object,
            'attributes' => array()
        );

        if (!is_null($limit)) {
            $cmd['attributes']['limit'] = $limit;
        }

        $this->send($cmd, $returnFullResponse);
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
