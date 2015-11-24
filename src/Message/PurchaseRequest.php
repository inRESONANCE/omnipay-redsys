<?php

namespace Omnipay\Redsys\Message;

use Omnipay\Common\Message\AbstractRequest;

/**
 * Redsys Purchase Request
 */
class PurchaseRequest extends AbstractRequest
{
    protected $liveEndpoint = 'https://sis.redsys.es/sis/realizarPago';
    protected $testEndpoint = 'https://sis-t.redsys.es:25443/sis/realizarPago';

    public function getMerchantCode()
    {
        return $this->getParameter('merchantCode');
    }

    public function setMerchantCode($value)
    {
        return $this->setParameter('merchantCode', $value);
    }

    public function getSecretKey()
    {
        return $this->getParameter('secretKey');
    }

    public function setSecretKey($value)
    {
        return $this->setParameter('secretKey', $value);
    }

    public function getTerminal()
    {
        return $this->getParameter('terminal');
    }

    public function setTerminal($value)
    {
        return $this->setParameter('terminal', $value);
    }

    public function getMerchantName()
    {
        return $this->getParameter('merchantName');
    }

    public function setMerchantName($value)
    {
        return $this->setParameter('merchantName', $value);
    }

    public function getLanguage()
    {
        return $this->getParameter('language');
    }

    public function setLanguage($value)
    {
        return $this->setParameter('language', $value);
    }

    public function getExtraData()
    {
        return $this->getParameter('extraData');
    }

    public function setExtraData($value)
    {
        return $this->setParameter('extraData', $value);
    }

    public function getAuthorisationCode()
    {
        return $this->getParameter('authorisationCode');
    }

    public function setAuthorisationCode($value)
    {
        return $this->setParameter('authorisationCode', $value);
    }

    public function getPayMethods()
    {
        return $this->getParameter('payMethods');
    }

    public function setPayMethods($value)
    {
        return $this->setParameter('payMethods', $value);
    }

    public function generateSignature($data) 
    {
        $signature = '';

        foreach (array('Ds_Merchant_Amount', 
                       'Ds_Merchant_Order', 
                       'Ds_Merchant_MerchantCode', 
                       'Ds_Merchant_Currency', 
                       'Ds_Merchant_TransactionType', 
                       'Ds_Merchant_MerchantURL') as $field) 
        {
            $signature .= $data[$field];
        }

        $signature .= $this->getSecretKey();
        $signature = sha1($signature);

        return $signature;
    }

    public function getData()
    {
        $this->validate('amount', 'currency', 'transactionId', 'merchantCode', 'terminal');

        $amount = str_replace('.', '', $this->getAmount());
        $order = str_pad($this->getTransactionId(), 12, '0', STR_PAD_LEFT);
        $card = $this->getCard();

        $data = array(
            'Ds_Merchant_Amount' => $amount,
            'Ds_Merchant_Currency' => $this->getCurrencyNumeric(),
            'Ds_Merchant_Order' => $order,
            'Ds_Merchant_ProductDescription' => $this->getDescription(),
            'Ds_Merchant_Titular' => $card->getName(),
            'Ds_Merchant_MerchantCode' => $this->getMerchantCode(),
            'Ds_Merchant_MerchantURL' => $this->getNotifyUrl(),
            'Ds_Merchant_UrlOK' => $this->getReturnUrl(),
            'Ds_Merchant_UrlKO' => $this->getCancelUrl(),
            'Ds_Merchant_MerchantName' => $this->getMerchantName(),
            'Ds_Merchant_ConsumerLanguage' => $this->getLanguage(),
            'Ds_Merchant_Terminal' => $this->getTerminal(),
            'Ds_Merchant_MerchantData' => $this->getExtraData(),
            'Ds_Merchant_TransactionType' => 0,
            'Ds_Merchant_AuthorisationCode' => $this->getAuthorisationCode(),
        );

        if ($this->getPayMethods()) 
        {
            $data['Ds_Merchant_PayMethods'] = $this->getPayMethods();
        }

        // $data['Ds_Merchant_MerchantSignature'] = $this->generateSignature($data);

        // return $data;

        $json = json_encode($data);

        $json = base64_encode($json);

        $data = array(
            'Ds_SignatureVersion' => 'HMAC_SHA256_V1',
            'Ds_MerchantParameters' => $json,
            'Ds_Signature' => $this->generateSignature($json)
        );

        return $data;
    }

    public function sendData($data)
    {
        return $this->response = new PurchaseResponse($this, $data);
    }

    public function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }

}
