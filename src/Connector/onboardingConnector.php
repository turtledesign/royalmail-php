<?php

namespace RoyalMail\Connector;

class onboardingConnector extends baseConnector {

  use remoteConnector;

  function getEndpoint() { return 'https://api.royalmail.com/shipping/onboarding'; }
}