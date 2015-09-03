<?php

namespace RoyalMail\Connector;

abstract class baseConnector {

  function request($params) {
    return $this->doRequest($params);
  }


  abstract protected function doRequest($config, $request_type, $params);

}