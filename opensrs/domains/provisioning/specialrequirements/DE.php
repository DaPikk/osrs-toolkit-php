<?php

namespace opensrs\domains\provisioning\specialrequirements;

use opensrs\Base;
use opensrs\Exception;

class DE extends Base
{
    protected $tld = 'de';
    protected $requiredFields = array(
        'owner_confirm_address',
        );

    public function __construct()
    {
        parent::__construct();
    }

    public function __destruct()
    {
        parent::__destruct();
    }

    public function meetsSpecialRequirements($dataObject)
    {
        return $this->validateSpecialFields($dataObject) && !$this->needsSpecialRequirements($dataObject);
    }

    public function validateSpecialFields($dataObject)
    {
        // Make sure all required fields defined in
        // $this->requiredFields array are assigned
        // values
        foreach ($this->requiredFields as $reqData) {
            if ($dataObject->data->$reqData == '') {
                throw new Exception('oSRS Error - '.$reqData.' is not defined.');
            }
        }

        return true;
    }

    public function needsSpecialRequirements($dataObject)
    {
        return false;
    }

    public function setSpecialRequestFieldsForTld($dataObject, $requestData)
    {
        $requestData['attributes']['owner_confirm_address'] = $dataObject->data->owner_confirm_address;

        return $requestData;
    }
}
