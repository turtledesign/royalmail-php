<?php

namespace RoyalMail\tests\unit\Filter;

use atoum;

class Filters extends atoum {
  use \RoyalMail\Validator\Validates;
  use \RoyalMail\Filter\Filters;


  function testPhoneCleaner() {

    $this
      ->string(self::doCleanUKPhone('+44 1234 567 890', ['stripCountryCode' => TRUE]))
      ->isEqualTo('01234 567 890');

    $this
      ->string(self::doCleanUKPhone('0044 1234 567 890', ['stripCountryCode' => TRUE]))
      ->isEqualTo('01234 567 890');

    $this
      ->string(self::doCleanUKPhone('+44 (0)1234 567 890', ['stripCountryCode' => TRUE, 'stripBlanks' => TRUE]))
      ->isEqualTo('01234567890');
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