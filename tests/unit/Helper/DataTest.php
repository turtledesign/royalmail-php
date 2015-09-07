<?php

namespace RoyalMail\tests\unit\Helper;

use atoum;

class Data extends atoum {


  function testServiceTypes() {
    $this
      ->given($this->newTestedInstance)
      ->array($this->testedInstance['service_types'])
      ->hasKey('T')
      ->contains('Tracked Returns');
  }
}