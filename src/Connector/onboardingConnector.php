<?php

namespace RoyalMail\Connector;

class onboardingConnector extends baseConnector {

  use soapConnector;

  function getEndpoint() { return 'https://api.royalmail.com/shipping/onboarding'; }
}