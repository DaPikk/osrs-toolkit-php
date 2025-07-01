<?php

namespace opensrs\event;

use opensrs\Base;

class PollAck extends Base
{
    public $action = 'poll';
    public $object = 'ack';

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

        $cookie = null;
        if (isset($dataObject->data->cookie)) {
            $cookie = $dataObject->data->cookie;
        } elseif (isset($dataObject->cookie)) {
            $cookie = $dataObject->cookie;
        }

        if (empty($cookie)) {
            throw new \Exception("PollAck requires 'cookie' parameter");
        }

        $cmd = array(
            'protocol' => 'XCP',
            'action' => $this->action,
            'object' => $this->object,
            'attributes' => array(
                'cookie' => $cookie
            )
        );

        $this->send($cmd, $returnFullResponse);
    }

    public function __destruct()
    {
        parent::__destruct();
    }
}
