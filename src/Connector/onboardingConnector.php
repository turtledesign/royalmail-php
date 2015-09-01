<?php

namespace RoyalMail\Connector

class onboardingConnector extends baseConnector {

  const ENDPOINT = 'https://api.royalmail.com/shipping/onboarding';

  use remoteConnector;
}