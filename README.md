# Omnipay: Redsys

**Redsys driver for the Omnipay PHP payment processing library for PHP 5.3+** 

This package implements Redsys support for Omnipay.

**Update (25-11-2015)**: Now working with the new HMAC SHA256 signature, mandatory with Redsys.

## Installation

Omnipay is installed via [Composer](http://getcomposer.org/). To install, simply add it
to your `composer.json` file:

```json
{
  "repositories": [
    {
      "type": "vcs",
      "url": "https://github.com/neverbot/omnipay-redsys"
    }
  ],
  "require": {
      "neverbot/omnipay-redsys": "~2.0"
  }
}
```

And run composer to update your dependencies:

```bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update
```

## Basic Usage

The following gateways are provided by this package:

* Redsys

For general usage instructions, please see the main [Omnipay](https://github.com/omnipay/omnipay)
repository.

## Using Laravel

If you are using the Laravel framework, I recommend using the [laravel-omnipay](https://github.com/ignited/laravel-omnipay) package.

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://github.com/neverbot/omnipay-redsys"
  }
],
"require": {
  "laravel/framework": "4.2.*",
  "ignited/laravel-omnipay": "1.*",
  "neverbot/omnipay-redsys": "dev-master"
},
```

Add a gateway to your `laravel-onmipay` `config.php`

```php
'gateways' => array(
	'Redsys' => array(
		'driver' => 'Redsys',
		'options' => array(
      'merchantCode' => 'your_merchant_code',
      'secretKey' => 'your_secret_key',
      'terminal' => 1,
      'testMode' => true,
		)
	)
)
```

Sending a payment to the Redsys platform

```php
Omnipay::setGateway('Redsys');

$buyerInfo = array(
  'firstName' => 'you_buyers_name',
);

$purchase = Omnipay::purchase([
  'amount' => '10.00', // decimals mandatory
  'transactionId' => 'your_generated_unique_transaction_id', // xxxxAAAAAAAA (4 numbers, 8 alphanumerics)
  'currency' => 'EUR',
  'description' => 'your_product_description',
  'notifyUrl' => 'your_notification_url', // redsys will send here a POST message
  'returnUrl' => 'your_confirmation_url', // buyer will be redirected here if purchase confirmed
  'cancelUrl' => 'your_cancel_url', // buyer will be redirected here if purchase cancelled or rejected
  'card' => $buyerInfo
]);

$response = $purchase->send();

if ($response->isSuccessful()) 
{
  // payment was successful: update database
  // print_r($response);
  return Redirect::route('whereever');
} 
elseif ($response->isRedirect())
{
  // redirect to offsite payment gateway
  // this is the usual way of doing this
  $response->redirect();
} 
else 
{
  // payment failed: display message to customer
  // echo $response->getMessage();
  return Redirect::route('wherever');
}
```
Receiving the Redsys notification (POST message)

```php
$info = Input::all();

Omnipay::setGateway('Redsys');

try
{
  $purchase = Omnipay::completePurchase($info);
  $response = $purchase->send();
}
catch(Exception $e)
{
  // maybe the response signature is wrong, do whatever you need here
  Log::error(' *************** Redsys error ***************');
  Log::error($e);      
}

// update your database, using $info['Ds_Order'] where the 
// unique transaction id is stored

if ($response->isSuccessful())
{
  // payment accepted 
}
else
{
  // payment cancelled or rejected
}
```

## Support

If you are having general issues with Omnipay, we suggest posting on
[Stack Overflow](http://stackoverflow.com/). Be sure to add the
[omnipay tag](http://stackoverflow.com/questions/tagged/omnipay) so it can be easily found.

If you want to keep up to date with release anouncements, discuss ideas for the project,
or ask more detailed questions, there is also a [mailing list](https://groups.google.com/forum/#!forum/omnipay) which
you can subscribe to.

If you believe you have found a bug, please report it using the [GitHub issue tracker](https://github.com/neverbot/omnipay-redsys/issues),
or better yet, fork the library and submit a pull request.
