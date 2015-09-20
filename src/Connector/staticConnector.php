<?php


namespace RoyalMail\Connector;

/**
 * Use static response files to enable testing without accessing any remote systems - allows testing before obtaining a Royal Mail Shipping API account.
 * 
 */
class staticConnector extends baseConnector {

  use soapConnector;

  function getEndpoint() { return dirname(__FILE__) . '/../../reference/ShippingAPI_V2_0_8.wsdl'; }

}