<?php

/**
 * Sample PHPUnit test to illustrate using the module with a minimal setup in development mode.
 * 
 * 
 */
class BasicUsageTest extends Test_Case_Unit {


/**
   * @group RoyalMail
   * 
   */
  function testInterfaceLoading() {
    $interface = new RoyalMail\RoyalMail;

    $this->assertTrue($interface instanceof RoyalMail\RoyalMail);

    $dev_helper = $interface->getDevelopmentHelper();

    $this->assertTrue($dev_helper instanceof RoyalMail\Helper\Development);

    $req_params = $dev_helper->getSampleRequest('cancelShipment');

    $this->assertTrue(is_array($req_params) && count($req_params) > 0);

    $response = $interface->cancelShipment($req_params);
    // $interface->processAction('cancelShipment', $req_params); // is an equivalent alternative syntax to call an API action.

    $this->assertTrue($response instanceof RoyalMail\Response\Interpreter);

    $this->assertTrue($response->succeeded());

    $this->assertEquals('RQ221150275GB', $response['cancelled_shipments'][0]);    
  }
}