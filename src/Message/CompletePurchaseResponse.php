<?php

namespace Omnipay\Redsys\Message;

use Omnipay\Common\Message\AbstractResponse;

/**
 * Redsys Response
 */
class CompletePurchaseResponse extends AbstractResponse
{
    public function isSuccessful()
    {
        if (isset($this->data['Ds_Response']) && $this->data['Ds_Response'] >= 0 && $this->data['Ds_Response'] < 100) 
        {
            return true;
        }
        
        return false;
    }

    public function isRedirect()
    {
        return false;
    }

    public function getTransactionReference() 
    {
        return $this->data['Ds_AuthorisationCode'];
    }

    public function getExtraData() 
    {
        return $this->data['Ds_MerchantData'];
    }

}
