<?php

namespace RoyalMail\Connector

class liveConnector extends baseConnector {

  use remoteConnector;

  function getEndpoint() { return 'https://api.royalmail.com/shipping'; }
}