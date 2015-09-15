<?php

namespace RoyalMail\tests\unit\Filter;

use atoum;

class Filters extends atoum {
  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;


  function testGBPhoneCleaner() {

    $this
      ->string(self::doCleanGBPhone('+44 1234 567 890', ['stripCountryCode' => TRUE]))
      ->isEqualTo('01234 567 890');

    $this
      ->string(self::doCleanGBPhone('0044 1234 567 890', ['stripCountryCode' => TRUE]))
      ->isEqualTo('01234 567 890');

    $this
      ->string(self::doCleanGBPhone('+44 (0)1234 567 890', ['stripCountryCode' => TRUE, 'stripBlanks' => TRUE]))
      ->isEqualTo('01234567890');
  }


  function testGBPostcodeFormatter() {
    $this->string(self::doFormatGBPostcode('EH10 4BF'))->isEqualTo('EH10 4BF'); // Correct
    $this->string(self::doFormatGBPostcode('Eh10 4bF'))->isEqualTo('EH10 4BF'); // Wrong Case
    $this->string(self::doFormatGBPostcode('Eh104bF'))->isEqualTo('EH10 4BF');  // No Space
    $this->string(self::doFormatGBPostcode('Eh104 bF'))->isEqualTo('EH10 4BF'); // Wrong Space
    
    $this
      ->string(self::doFormatGBPostcode('Eh104 bF', ['check_country' => 'input:country'], ['input' => ['country' => 'GB']]))
      ->isEqualTo('EH10 4BF'); // Wrong Space with country check == GB

    $this
      ->string(self::doFormatGBPostcode('123456', ['check_country' => 'input:country'], ['input' => ['country' => 'FR']]))
      ->isEqualTo('123456'); // Non GB Postcode
  }



  function testSkipIfEmpty() {
    $schema = ['_pre_filter' => ['SkipThisIfThatEmpty' => 'input:bar']];

    $helper = ['input' => ['bar' => 'bq', 'baz' => 'inga']];

    $this->string(self::filter('foo', $schema, 'pre', $helper))->isEqualTo('foo');

    $helper['input']['bar'] = '';

    $this
      ->exception(function () use ($schema, $helper) { self::filter('foo', $schema, 'pre', $helper); })
      ->isInstanceOf('\RoyalMail\Exception\BuilderSkipFieldException');
  }
}