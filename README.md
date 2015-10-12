# Royal Mail API SDK for PHP

[![Build Status](https://travis-ci.org/turtledesign/royalmail-php.png?branch=master)](https://travis-ci.org/turtledesign/royalmail-php)

This repository contains a PHP SDK/Interface for the (UK) [Royal Mail's Shipping API](http://www.royalmail.com/corporate/services/shipping-api)

> This module is in an alpha state and hasn't yet been tested against the Royal Mail's servers.  
> It should be now be possible to use it in development mode to integrate with client systems, see the [documentation (also still being created) in the wiki](https://github.com/turtledesign/royalmail-php/wiki) 
> This is not an official SDK, we (http://www.turtledesign.com/) are a 3rd party integrator releasing the module with an open source licence because why not.

## Prerequisites

   - PHP 5.4 or above
   - [soap](http://php.net/manual/en/book.soap.php) & [openssl](http://php.net/manual/en/book.openssl.php) extensions must be enabled
   - [fileinfo](http://php.net/manual/en/book.fileinfo.php) required for testing.

## Installation

### - Using Composer
[**composer**](https://getcomposer.org/) is the recommended way to install the SDK. To use the SDK with project, add the following dependency to your application's composer.json and run `composer update --no-dev` to fetch the SDK.

You can download composer using instructions on [Composer Official Website.](https://getcomposer.org/download/)

#### Prerequisites
- *composer* for fetching dependencies (See [http://getcomposer.org](http://getcomposer.org))

#### Steps to Install :

Currently, the SDK is available at [https://packagist.org](https://packagist.org/packages/turtledesign/royalmail-php). To use it in your project, you need to include it as a dependency in your project composer.json file. It can be done in two ways :

* Running `composer require 'turtledesign/royalmail-php:*@dev'` command on your project root location (where project composer.json is located.)

* Or, manually editing composer.json file `require` field, and adding `"turtledesign/royalmail-php" :  "*@dev"` inside it.

The resultant sample *composer.json* would look like this:

```php
{
  ...

  "name": "sample/website",
  "require": {
  	"turtledesign/royalmail-php" : "*@dev"
  }

  ...
}
```

### - Direct Download (without using Composer)

If you do not want to use composer, you can grab the SDK zip that contains Royal Mail API SDK with all its dependencies with it.

#### Steps to Install :
- Download zip archive with desired version from our [Releases](https://github.com/turtledesign/royalmail-php/releases). Each release will have a `direct-download-*.zip` that contains PHP Rest API SDK and its dependencies.

- Unzip and copy vendor directory inside your project, e.g. project root directory.

- If your application has a bootstrap/autoload file, you should add
`include '<vendor directory location>/vendor/autoload.php'` in it. The location of the `<vendor directory>` should be replaced based on where you downloaded **vendor** directory in your application.

- This *autoload.php* file registers a custom autoloader that can autoload the Royal Mail SDK files, that allows you to access PHP SDK system in your application.



## More help

   * [Royal Mail Shipping API Service Page](http://www.royalmail.com/corporate/services/shipping-api)
   * [API Reference - PDF](http://www.royalmail.com/sites/default/files/Shipping-API-Technical-User-Guide-v2_1-June-2015.pdf)
   * [Reporting issues / feature requests](https://github.com/turtledesign/royalmail-php/issues)
