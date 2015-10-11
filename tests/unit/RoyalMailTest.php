<?php

namespace RoyalMail\tests\unit;

use atoum;

class RoyalMail extends atoum {


  function testHelperFactory() {
    $this
      ->given($this->newTestedInstance)
      ->object($this->testedInstance->getDataHelper())
      ->isInstanceOf('\RoyalMail\Helper\Data');
  }


  function testConnectorFactory() {
    // Development
    $this
      ->given($this->newTestedInstance)
      ->object($connector = $this->testedInstance->getConnector())
      ->isInstanceOf('\RoyalMail\Connector\soapConnector')
      ->object($connector->getSoapClient())
      ->isInstanceOf('\RoyalMail\Connector\MockSoapClient');
  }


  // function testBuildRequest() {

  // }
}