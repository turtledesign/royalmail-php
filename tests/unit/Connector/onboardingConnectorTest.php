<?php

namespace RoyalMail\tests\unit\Connector;

use atoum;

class onboardingConnector extends atoum {


  /**
   * More or less just a smoke test - but will need updating if the endpoint does.
   * 
   */
  function testGetEndpoint() {
    $this->given($this->newTestedInstance)
      ->string($this->testedInstance->getEndpoint())
      ->isEqualTo('https://api.royalmail.com/shipping/onboarding');
  }
}
