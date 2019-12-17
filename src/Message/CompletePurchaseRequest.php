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
        $l = ceil(strlen($message) / 8) * 8;
        $ciphertext = substr(openssl_encrypt($message . str_repeat("\0", $l - strlen($message)), 'des-ede3-cbc', $key, OPENSSL_RAW_DATA, "\0\0\0\0\0\0\0\0"), 0, $l);

        return $ciphertext;
    }

    public function checkSignature($data, $signature)
    {
        $json = json_encode($data);
        $json = base64_encode($json);

        // decode the key base64
        $key = base64_decode($this->getSecretKey());
        $key = $this->encrypt_3DES($data['Ds_Order'], $key);

        // MAC256 of the parameters
        $res = hash_hmac('sha256', $json, $key, true); // (PHP 5 >= 5.1.2)

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
