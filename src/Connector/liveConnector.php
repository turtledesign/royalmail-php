<?php

namespace RoyalMail\Connector

class liveConnector extends baseConnector {

  const ENDPOINT = 'https://api.royalmail.com/shipping';

  use remoteConnector;

}