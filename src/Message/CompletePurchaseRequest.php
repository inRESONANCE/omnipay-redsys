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

        $parameters = $query->get('Ds_MerchantParameters');
        $parameters = base64_decode(strtr($parameters, '-_', '+/'));

        Illuminate\Log\Log::info($parameters);

        $data = array();

        foreach (array('Ds_Date', 
                       'Ds_Hour', 
                       'Ds_Amount', 
                       'Ds_Currency', 
                       'Ds_Order', 
                       'Ds_MerchantCode', 
                       'Ds_Terminal', 
                       'Ds_Signature', 
                       'Ds_Response', 
                       'Ds_TransactionType', 
                       'Ds_SecurePayment', 
                       'Ds_MerchantData', 
                       'Ds_Card_Country', 
                       'Ds_AuthorisationCode', 
                       'Ds_ConsumerLanguage', 
                       'Ds_Card_Type') as $field) 
        {
            $data[$field] = $parameters[$field];
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
