<?php

namespace RoyalMail\Connector;

class TDSoapClient extends \SoapClient {


  function __doRequest($request, $location, $action, $version, $one_way = 0) {
    return '';
  }

}