<?php

namespace Omnipay\Redsys\Message;

use Omnipay\Common\Exception\InvalidResponseException;

/**
 * Redsys Complete Purchase Request
 */
class CompletePurchaseRequest extends PurchaseRequest
{
    protected function encrypt_3DES($message, $key)
    {
        $bytes = array(0,0,0,0,0,0,0,0);
        $iv = implode(array_map("chr", $bytes)); // PHP 4 >= 4.0.2

        $ciphertext = mcrypt_encrypt(MCRYPT_3DES, $key, $message, MCRYPT_MODE_CBC, $iv); // PHP 4 >= 4.0.2
        return $ciphertext;
    }

    public function checkSignature($data, $signature) 
    {
        // decode the key base64
        $key = base64_decode($this->getSecretKey());
        $key = $this->encrypt_3DES($data['Ds_Order'], $key);

        // MAC256 of the parameters
        $res = hash_hmac('sha256', $data, $key, true); // (PHP 5 >= 5.1.2)

        // decode data base64
        $newSignature = strtr(base64_encode($res), '+/', '-_');

        return $signature == $newSignature;
    }

    public function getData()
    {
        $query = $this->httpRequest->request;

        $signature = $query->get('Ds_Signature');

        $parameters = $query->get('Ds_MerchantParameters');
        $parameters = base64_decode(strtr($parameters, '-_', '+/'));
        $parameters = json_decode($parameters, true); // (PHP 5 >= 5.2.0)

        if (!$this->checkSignature($parameters, $signature)) 
        {
            throw new InvalidResponseException('Invalid signature: ' . $signature);
        }

        return $parameters;
    }

    public function sendData($data)
    {
        return $this->response = new CompletePurchaseResponse($this, $data);
    }
}
