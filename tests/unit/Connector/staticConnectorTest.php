<?php

namespace RoyalMail\tests\unit\Connector;

use atoum;
use \RoyalMail\Request\Builder as ReqBuilder;

class staticConnector extends atoum {

  use \RoyalMail\tests\TestDataLoader;

  /**
   * More or less just a smoke test - but will need updating if the endpoint does.
   */
  function testGetEndpoint() {
    $this
      ->given($this->newTestedInstance)
      ->string($this->testedInstance->getEndpoint())
      ->contains('ShippingAPI_V2_0_8.wsdl');
  }



  function testXMLGeneration() {
    $test_schema = $this->getTestSchema('request_builder');

    $request  = array_merge($test_schema['cancelShipment']['valid']['request'], ['integrationHeader' => $test_schema['integrationHeader']['valid']['request']]);
    $response = array_merge($test_schema['cancelShipment']['valid']['expect'], ['integrationHeader' => $test_schema['integrationHeader']['valid']['expect']]);

    $helper = new \RoyalMail\Helper\Data();

    $this
      ->array($req_params = ReqBuilder::build('cancelShipment', $request, $helper))
      ->isEqualTo($response);

    // $this
    //   ->given($this->newTestedInstance)
    //   ->string($this->testedInstance->doRequest('cancelShipment', $req_params))
    //   ->isEqualTo('');


  }
}
