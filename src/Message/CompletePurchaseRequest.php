<?php

namespace Omnipay\Redsys\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Redsys Complete Purchase Request
 */
class CompletePurchaseRequest extends PurchaseRequest
{
    public function checkSignature($data) 
    {
        // if (!isset($data['Ds_Signature'])) 
        // {
        //     return false;
        // }

        // $signature = '';

        // foreach (array('Ds_Amount', 'Ds_Order', 'Ds_MerchantCode', 'Ds_Currency', 'Ds_Response') as $field) 
        // {
        //     if (isset($data[$field])) 
        //     {
        //         $signature .= $data[$field];
        //     }
        // }
        // $signature .= $this->getSecretKey();
        // $signature = sha1($signature);

        // return $signature == strtolower($data['Ds_Signature']);

        return true;
    }

    public function getData()
    {
        $query = $this->httpRequest->request;

        $data = array();

        foreach (array( 'Ds_SignatureVersion', 'Ds_Signature', 'Ds_MerchantParameters' ) as $field) 
        {
            $data[$field] = $query->get($field);
        }

        if (!$this->checkSignature($data)) 
        {
            throw new InvalidResponseException('Invalid signature: ' . $data['Ds_Signature']);
        }

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}
