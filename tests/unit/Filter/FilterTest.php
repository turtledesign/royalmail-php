<?php

namespace RoyalMail\tests\unit\Filter;

use atoum;

class Filter extends atoum {
  use \RoyalMail\Filter\Filter;


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
}