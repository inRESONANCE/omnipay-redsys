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
        // Se decodifica la clave Base64
        $key = base64_decode($this->getSecretKey());

        // $order = str_pad($this->getTransactionId(), 12, '0', STR_PAD_LEFT);

        // Se diversifica la clave con el Número de Pedido
        $key = $this->encrypt_3DES($data['Ds_Order'], $key);

        // MAC256 del parámetro Ds_Parameters que envía Redsys
        $res = hash_hmac('sha256', $data, $key, true); // (PHP 5 >= 5.1.2)

        // Se codifican los datos Base64
        $newSignature = strtr(base64_encode($res), '+/', '-_');

        \Log::info('secretKey: ' . $this->getSecretKey());
        \Log::info('signature: ' . $signature);
        \Log::info('newSignature: ' . $newSignature);

        return $signature == $newSignature;

        // return true;
    }

    public function getData()
    {
        $query = $this->httpRequest->request;

        $signature = $query->get('Ds_Signature');

        $parameters = $query->get('Ds_MerchantParameters');
        $parameters = base64_decode(strtr($parameters, '-_', '+/'));
        $parameters = json_decode($parameters, true); // (PHP 5 >= 5.2.0)

        // \Log::info($parameters);

        /*
        $data = array();

        foreach (array('Ds_Date', 
                       'Ds_Hour', 
                       'Ds_SecurePayment', 
                       'Ds_Card_Country', 
                       'Ds_Amount', 
                       'Ds_Currency', 
                       'Ds_Order', 
                       'Ds_MerchantCode', 
                       'Ds_Terminal', 
                       'Ds_Response', 
                       'Ds_MerchantData', 
                       'Ds_TransactionType', 
                       'Ds_ConsumerLanguage', 
                       'Ds_AuthorisationCode') as $field) 
        {
            $data[$field] = $parameters[$field];
        }
        */

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
