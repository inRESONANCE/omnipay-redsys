<?php

namespace Omnipay\Redsys;

use Omnipay\Common\AbstractGateway;
use Omnipay\Dummy\Message\AuthorizeRequest;

/**
 * Redsys Gateway
 */
class Gateway extends AbstractGateway
{
    public function getName()
    {
        return 'Redsys';
    }

    public function getDefaultParameters()
    {
        return array(
            'merchantCode' => '',
            'secretKey' => '',
            'terminal' => 1,
            'testMode' => false,
            'language' => 1,
            'merchantName' => 'Omnipay'
        );
    }

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

    public function getPayMethods()
    {
        return $this->getParameter('payMethods');
    }

    public function setPayMethods($value)
    {
        return $this->setParameter('payMethods', $value);
    }

    public function purchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Redsys\Message\PurchaseRequest', $parameters);
    }

    public function completePurchase(array $parameters = array())
    {
        return $this->createRequest('\Omnipay\Redsys\Message\CompletePurchaseRequest', $parameters);
    }
}
