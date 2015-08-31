# SOAP API SDK for PHP

[![Build Status](https://travis-ci.org/paypal/PayPal-PHP-SDK.png?branch=master)](https://travis-ci.org/paypal/PayPal-PHP-SDK) [![Coverage Status](https://img.shields.io/coveralls/paypal/PayPal-PHP-SDK.svg)](https://coveralls.io/r/paypal/PayPal-PHP-SDK?branch=master) [![Latest Stable Version](https://poser.pugx.org/paypal/rest-api-sdk-php/v/stable.png)](https://packagist.org/packages/paypal/rest-api-sdk-php) [![Total Downloads](https://poser.pugx.org/paypal/rest-api-sdk-php/downloads.png)](https://packagist.org/packages/paypal/rest-api-sdk-php)

This repository contains a PHP SDK/Interface for the (UK) [Royal Mail's Shipping API](http://www.royalmail.com/corporate/services/shipping-api)

> Currently development is just started - this readme is 0.5 inched from the PayPal SDK and should become more original as development progresses (PayPal Readme writers: if my usage angers thee drop me a line and I'll rewrite quicker!)
> This is not an official SDK, we (turtledesign.com) are a 3rd party integrator releasing the module with an open source licence because why not.

## Prerequisites

   - PHP 5.4 or above
   - [curl](http://php.net/manual/en/book.curl.php)  & [openssl](http://php.net/manual/en/book.openssl.php) extensions must be enabled

## Installation

### - Using Composer
[**composer**](https://getcomposer.org/) is the recommended way to install the SDK. To use the SDK with project, add the following dependency to your application's composer.json and run `composer update --no-dev` to fetch the SDK.

You can download composer using instructions on [Composer Official Website.](https://getcomposer.org/download/)

#### Prerequisites
- *composer* for fetching dependencies (See [http://getcomposer.org](http://getcomposer.org))

#### Steps to Install :

Currently, Paypal PHP Rest API SDK is available at [https://packagist.org](https://packagist.org/packages/turtledesign/royalmail-php). To use it in your project, you need to include it as a dependency in your project composer.json file. It can be done in two ways :

* Running `composer require turtledesign/royalmail-php:*` command on your project root location (where project composer.json is located.)

* Or, manually editing composer.json file `require` field, and adding `"turtledesign/royalmail-php" :  "*"` inside it.

The resultant sample *composer.json* would look like this:

```php
{
  ...

  "name": "sample/website",
  "require": {
  	"turtledesign/royalmail-php" : "*"
  }

  ...
}
```

### - Direct Download (without using Composer)

If you do not want to use composer, you can grab the SDK zip that contains Paypal PHP Rest API SDK with all its dependencies with it.

#### Steps to Install :
- Download zip archive with desired version from our [Releases](https://github.com/turtledesign/royalmail-php/releases). Each release will have a `direct-download-*.zip` that contains PHP Rest API SDK and its dependencies.

- Unzip and copy vendor directory inside your project, e.g. project root directory.

- If your application has a bootstrap/autoload file, you should add
`include '<vendor directory location>/vendor/autoload.php'` in it. The location of the `<vendor directory>` should be replaced based on where you downloaded **vendor** directory in your application.

- This *autoload.php* file registers a custom autoloader that can autoload the PayPal SDK files, that allows you to access PHP SDK system in your application.



## More help

   * [API Reference](http://www.royalmail.com/sites/default/files/Shipping-API-Technical-User-Guide-v2_1-June-2015.pdf)
   * [Reporting issues / feature requests](https://github.com/turtledesign/royalmail-php/issues)
