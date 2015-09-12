<?php

namespace RoyalMail\Connector

class liveConnector extends baseConnector {

  use soapConnector;

  function getEndpoint() { return 'https://api.royalmail.com/shipping'; }
}